<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;
use CodeIgniter\Shield\Traits\Resettable as Resettable;
use App\Libraries\UserProfileStructure;
use \Gumlet\ImageResize;
use Rogervila\ArrayDiffMultidimensional;

class UserModel extends ShieldUserModel
{
  use Resettable;

  const BASE_FIELDS = [
    'first_name', 'middle_name', 'last_name', 'bio', 'public_profile', 'email',
    'prefix', 'preferred_pronouns', 'mobile_phone_number', 'reintake',
  ];

  const AUDIT_SKIP_FIELDS = [
    'updated_at', 'full_text_search',
  ];

  const REVIEW_TRIGGER_FIELDS = [
    'bio', 'email', 'website_link', 'twitter_link', 'mastodon_link',
    'linkedin_link', 'instagram_link', 'facebook_link', 'snapchat_link',
  ];

  const REINTAKE_STATUS_NOT_REQUIRED = 'not_required';
  const REINTAKE_STATUS_REQUIRED = 'required';
  const REINTAKE_STATUS_ACCEPTED = 'accepted';
  const REINTAKE_STATUS_DENIED = 'denied';

  protected function initialize(): void
  {
    parent::initialize();
  }

    /**
   * Retrieves the profile data of a user.
   *
   * @param int $id      The ID of the user.
   *
   * @return array|null  The profile data of the user or null if not found.
   */
  public function getUserProfileData($id) {
    $db = \Config\Database::connect();
    $builder = $db->table('people');
    $userData = [];

    $baseUserData = $builder
      ->select('people.*')
      ->where('people.user_id', $id)
      ->get()
      ->getRowArray();

    if ($baseUserData === null) {
      return null;
    }

    if ($baseUserData !== null) {
      $userData = array_merge($userData, $baseUserData);

      $customFieldsData = $builder
        ->select('
          custom_field_data.value,
          custom_field_data.value_json,
          custom_fields.machine_name,
          custom_fields.input_type,
          custom_field_data.parent_field_value_index,
          custom_fields.metadata
        ')
        ->join('custom_field_data', 'custom_field_data.model_id = people.id', 'left')
        ->join('custom_fields', 'custom_fields.id = custom_field_data.custom_field_id', 'left')
        ->where('custom_fields.model_name = \'People\'')
        ->where('people.user_id', $id)
        ->get()
        ->getResultArray();

      $userCustomFields = [];
      foreach ($customFieldsData as $customFieldRecord) {
        $value = $customFieldRecord['value'];
        if (in_array($customFieldRecord['input_type'], ['tags_range', 'tags'])) {
          $value = json_decode($customFieldRecord['value_json'], true);
        }

        if ($customFieldRecord['input_type'] === 'multi') {
          $multiValues = [];
          $fieldMetadata = json_decode($customFieldRecord['metadata'], true);
          $childFieldsIds = array_map(fn($childField) => $childField['id'], $fieldMetadata['childFields']);
          $customFieldsModel = model('CustomFieldModel');
          $customFields = $customFieldsModel
            ->select('id, machine_name, input_type')
            ->whereIn('id', $childFieldsIds)
            ->findAll();

          foreach ($customFields as $customField) {
            $childFieldValues = array_filter($customFieldsData, fn($customFieldData) => $customFieldData['machine_name'] === $customField['machine_name'] && $customFieldData['parent_field_value_index'] !== null);

            foreach ($childFieldValues as $childFieldValue) {
              if (isset($multiValues[$childFieldValue['parent_field_value_index']]) === false) {
                $multiValues[$childFieldValue['parent_field_value_index']] = [];
              }

              if ($customField['input_type'] === 'tags') {
                $multiValues[$childFieldValue['parent_field_value_index']][$childFieldValue['machine_name']] = json_decode($childFieldValue['value_json']);
              } else {
                $multiValues[$childFieldValue['parent_field_value_index']][$childFieldValue['machine_name']] = $childFieldValue['value'];
              }
            }
          }

          $value = $multiValues;
        }

        $userCustomFields[$customFieldRecord['machine_name']] = $value;
      }

      $userData = array_merge($userData, $userCustomFields);
    }

    return $userData;
  }

  /**
   * Retrieves the structure of the user profile.
   *
   * @return array  The structure of the user profile.
   */
  public function getUserProfileStructure() {
    $userProfileStructure = new UserProfileStructure();
    return $userProfileStructure->getUserProfileStructure();
  }

  /**
   * Saves profile data to the database.
   *
   * @param array $requestData  The data containing profile fields to be saved.
   * @param int   $userId       The ID of the user whose profile is being saved (optional).
   *
   * @return array              An array containing transaction status and a response message.
   */
  public function saveProfileData($requestData, $userId = null) {
    $peopleModel = new PeopleModel();

    // Regular user editing
    if (is_null($userId)) {
      $userId = auth()->id();
    }

    // Edit other user profile as admin
    if (auth()->user()->can('admin.access') === true && isset($requestData['user_id']) && $requestData['user_id']) {
      $userId = $requestData['user_id'];
    }

    $userId = intval($userId);

    // Clear related caches
    $cache = \Config\Services::cache();
    $cache->delete("person_{$userId}");
    $cache->delete('filters_with_values');
    $cachePeopleSearchPath = ROOTPATH . 'writable/cache/people_*';
    exec("rm {$cachePeopleSearchPath} > /dev/null 2> /dev/null");

    $existingPerson = $peopleModel->where('user_id', $userId)->first();

    if ($existingPerson) {
      $oldProfileData = $this->getUserProfileData($userId);
    }

    $mappedData = [];
    foreach (UserModel::BASE_FIELDS as $key) {
      $mappedData[$key] = $requestData[$key] ?? '';

      if (is_string($mappedData[$key])) {
        $mappedData[$key] = strip_tags($mappedData[$key]);
      }
    }
    $data = $mappedData;

    $data['public_profile'] = $requestData['public_profile'] ?? false;
    $data['user_id'] = $userId;

    $data['full_text_search'] = array_map(function ($field) {
      if (is_array($field)) {
        return json_encode($field);
      }

      return $field;
    }, $requestData);
    $data['full_text_search'] = strtolower(
      str_replace(
        ['"', '[', ']', '{', '}'],
        '',
        join(' ', $data['full_text_search']
      )
    ));

    $peopleModel->db->transStart();

    if ($existingPerson) {
      $peopleModel->update($existingPerson['id'], $data);
    } else {
      $peopleModel->insert($data);
    }

    $record = $peopleModel->where('user_id', $userId)->first();
    if (!empty($record)) {
      $this->saveCustomFieldsProfileData($requestData, $record['id'], $record);
    }

    $peopleModel->db->transComplete();

    if ($existingPerson) {
      $this->addAuditRecord($oldProfileData, $existingPerson, $userId);
    }

    $message = $existingPerson ? 'Profile updated successfully' : 'Profile created successfully';

    return [$this->db->transStatus(), $message];
  }

  /**
   * Saves custom fields profile data to the database.
   *
   * @param array  $requestData      The data containing custom fields to be saved.
   * @param int    $userId           The ID of the user to whom the custom fields belong.
   * @param array  $personBasicData  Basic data of the person (optional).
   *
   * @return bool                    Returns true on success or false on failure.
   */
  private function saveCustomFieldsProfileData($requestData, $userId, $personBasicData) {
    try {
      if ($requestData['current_city'] || $requestData['home_city']) {
        $geoApiKey = $_ENV['geocode_mapbox_api.key'];
        $geoQueryArray = [$requestData['current_city'], $requestData['current_state'], $requestData['current_country']];
        $geoQueryArray = array_filter($geoQueryArray);
        if (count($geoQueryArray) === 0) {
          $geoQueryArray = [$requestData['home_city'], $requestData['home_state'], $requestData['home_country']];
          $geoQueryArray = array_filter($geoQueryArray);
        }
        $geoQuery = urlencode(join(',', $geoQueryArray));
        $geoApiResponse = json_decode(file_get_contents("https://api.mapbox.com/geocoding/v5/mapbox.places/{$geoQuery}.json?access_token={$geoApiKey}"), true);

        if (count($geoApiResponse['features']) > 0) {
          $requestData['current_location_lon'] = strval($geoApiResponse['features'][0]['center'][0]);
          $requestData['current_location_lat'] = strval($geoApiResponse['features'][0]['center'][1]);
        }
      }
    } catch (\Throwable $exception) {
      error_log($exception->getMessage());
    }

    $peopleModel = new PeopleModel();
    $dataKeys = array_keys($requestData);

    if (empty($dataKeys)) {
      return false;
    }

    $db = \Config\Database::connect();
    $builder = $db->table('custom_fields');

    $customFieldsToProcess = $builder
      ->select('
        custom_fields.*
      ')
      ->where('parent_field_id IS NULL')
      ->whereIn('machine_name', $dataKeys)
      ->get()
      ->getResultArray();

    $customFieldsData = [];
    foreach ($customFieldsToProcess as $customFieldToProcess) {
      $value = $requestData[$customFieldToProcess['machine_name']];
      $fieldMetadata = json_decode($customFieldToProcess['metadata'], true);

      $fieldData = [
        'custom_field_id' => $customFieldToProcess['id'],
        'model_id' => $userId,
      ];

      switch ($customFieldToProcess['input_type']) {
        case 'tags':
        case 'tags_range':
          if ($customFieldToProcess['input_type'] === 'tags_range') {
            if (is_array($value)) {
              $value = array_map(function ($v) {
                if (isset($v['tags']) && is_array($v['tags']) === false) {
                  $v['tags'] = [$v['tags']];
                }

                return $v;
              }, $value);
            }
          }

          if ($customFieldToProcess['input_type'] === 'tags') {
            if (is_array($value)) {
              $value = array_map(function ($v) {
                $v = trim($v);

                return $v;
              }, $value);
            } else {
              $value = [$value];
            }
          }

          $fieldData['value_json'] = json_encode($value);
          $fieldData['value'] = '';

          break;
        case 'multi':
          if (isset($fieldMetadata['childFields']) && count($fieldMetadata['childFields']) > 0) {
            $childFieldsData = [];
            $childFieldsIds = array_map(fn($childField) => $childField['id'], $fieldMetadata['childFields']);
            $customFieldsModel = model('CustomFieldModel');
            $customFields = $customFieldsModel
              ->select('id, machine_name, input_type')
              ->whereIn('id', $childFieldsIds)
              ->findAll();

            $db
              ->table('custom_field_data')
              ->whereIn('custom_field_id', $childFieldsIds)
              ->where('model_id', $userId)
              ->delete();

            if (is_array($value) === false) {
              continue 2;
            }

            foreach ($value as $index => $itemData) {
              foreach ($itemData as $machineName => $itemDataValue) {
                $itemField = current(array_filter($customFields, fn($customFieldsItem) => $customFieldsItem['machine_name'] === $machineName));

                if (!isset($itemField['id'])) {
                  continue;
                }

                if ($itemField['input_type'] === 'tags') {
                  $childFieldsData[] = [
                    'custom_field_id' => $itemField['id'],
                    'model_id' => $userId,
                    'parent_field_value_index' => $index,
                    'value' => '',
                    'value_json' => json_encode($itemDataValue),
                  ];
                } else {
                  $childFieldsData[] = [
                    'custom_field_id' => $itemField['id'],
                    'model_id' => $userId,
                    'parent_field_value_index' => $index,
                    'value' => $itemDataValue,
                    'value_json' => '[]',
                  ];
                }
              }
            }

            if (!empty($childFieldsData)) {
              $builder = $db->table('custom_field_data');
              $result = $builder
                ->onConstraint(['custom_field_id', 'model_id', 'parent_field_value_index'])
                ->upsertBatch($childFieldsData);
            }

            $fieldData['value'] = '';
            $fieldData['value_json'] = '[]';
          }

          break;
        default:
          $fieldData['value'] = strip_tags($value);
          $fieldData['value_json'] = '[]';

          break;
      }

      if (!($personBasicData && $personBasicData['image_url']) &&
        (isset($fieldMetadata['isImportProfileImageLink']) && $fieldMetadata['isImportProfileImageLink'] === true)
      ) {
        $dirPath = ROOTPATH . 'writable/uploads/profile_images';
        if ($value && $profileImageName = $this->downloadRemoteImage($value, $dirPath)) {
          $peopleModel->update($personBasicData['id'], [
            'image_url' => $profileImageName,
          ]);
        }
      }

      $customFieldsData[] = $fieldData;
    }

    if (!empty($customFieldsData)) {
      $builder = $db->table('custom_field_data');
      $result = $builder
        ->onConstraint(['custom_field_id', 'model_id', 'parent_field_value_index'])
        ->upsertBatch($customFieldsData);
    } else {
      $result = true;
    }

    return $result;
  }

  /**
   * Downloads a remote image and saves it to the local directory after resizing.
   *
   * @param string $remoteURL      The URL of the remote image.
   * @param string $localDirectory The directory where the image will be saved locally.
   *
   * @return string|false          The filename of the saved image on success, or false on failure.
   */
  private function downloadRemoteImage($remoteURL, $localDirectory) {
    helper('filesystem');

    try {
      $imageData = file_get_contents($remoteURL);

      if ($imageData !== false) {
        $remoteURLParts = explode('?', $remoteURL);
        $extension = pathinfo($remoteURLParts[0], PATHINFO_EXTENSION);
        $randomFileName = md5(uniqid('', true)) . '.' . $extension;
        $tempFilePath = sys_get_temp_dir() . '/' . $randomFileName;

        if (!write_file($tempFilePath, $imageData)) {
          throw new \Exception("Couldn't save image {$tempFilePath}.");
        }

        $mime = mime_content_type($tempFilePath);
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/bmp'];

        if (!in_array($mime, $allowedMimeTypes)) {
          unlink($tempFilePath);
          throw new \Exception("Wrong mime type {$tempFilePath}.");
        }

        $localFilePath = $localDirectory . '/' . $randomFileName;
        copy($tempFilePath, $localFilePath);
        unlink($tempFilePath);

        $image = new ImageResize($localFilePath);
        $image->resizeToWidth(300);
        $image->save($localFilePath);

        return $randomFileName;
      } else {
        throw new \Exception('Remote image contents empty.');
      }
    } catch (\Exception $e) {
      log_message('error', "Couldn't fetch image {$remoteURL}.");
      return false;
    }
  }

  private function getChildCustomFields() {
    $db = \Config\Database::connect();
    $builder = $db->table('custom_fields cf');
    $childFields = $builder
      ->select('cf.machine_name')
      ->where('model_name', 'People')
      ->where('parent_field_id IS NOT NULL')
      ->get()
      ->getResultArray();

    $childFields = array_map(function ($field) {
      return $field['machine_name'];
    }, $childFields);

    return $childFields;
  }

  /**
   * Add an audit record for a user profile change.
   *
   * This function creates an audit record if there are changes in the profile data of an existing person.
   * It compares the old profile data with the new data and logs the differences, excluding certain fields.
   * If changes are found, they are recorded in the 'audit' table. Additionally, if any changes require 
   * review (as defined by REVIEW_TRIGGER_FIELDS), an email notification is sent to the designated reviewers.
   *
   * @param array $oldProfileData The previous profile data before the update.
   * @param array $existingPerson The existing person's data.
   * @param int $userId The ID of the user whose profile is being updated.
   */
  private function addAuditRecord($oldProfileData, $existingPerson, $userId) {
    // Create audit record if needed
    if ($existingPerson) {
      $keysToSkip = array_merge(self::AUDIT_SKIP_FIELDS, $this->getChildCustomFields());
      $newProfileData = $this->getUserProfileData($userId);
      $newValues = ArrayDiffMultidimensional::compare($newProfileData, $oldProfileData, false);
      $newValues = array_diff_key($newValues, array_flip($keysToSkip));
      $oldValues = ArrayDiffMultidimensional::compare($oldProfileData, $newProfileData, false);
      $oldValues = array_diff_key($oldValues, array_flip($keysToSkip));
      $review = 'not_required';

      // Admin changes don't need to be reviewed
      $reviewNeeded = array_intersect(array_keys($newValues), self::REVIEW_TRIGGER_FIELDS) && auth()->user()->id === $userId;

      if ($reviewNeeded) {
        $review = 'required';
      }

      if (count($newValues) > 0) {
        $db = \Config\Database::connect();
        $builder = $db->table('audit');
        $builder->insert([
          'audited_id' => $userId,
          'model_name' => 'People',
          'changed_user_id' => auth()->id(),
          'changes' => json_encode([
            'new' => $newValues,
            'old' => $oldValues,
          ]),
          'review' => $review,
        ]);

        if ($reviewNeeded) {
          $review = 'required';

          $email = \Config\Services::email();
          $name = $existingPerson['first_name'] . ' ' . $existingPerson['last_name'];
          $userEmail = '';
          if ($existingPerson['email']) {
            $userEmail = ' ' . $existingPerson['email'];
          }
          $insertedId = $db->insertID();
          $dataAuditLink = site_url("admin/profile_data_audit/{$insertedId}");
          $email->setTo($_ENV['profileDataAudit.reviewEmails']);
          $email->setSubject("Profile edit made by {$name}{$userEmail}");
          $email->setMessage("
            Action required to accept profile changes made by {$name}{$userEmail}.<br><br>" . "
            Click to process it: <a href=\"{$dataAuditLink}\">{$dataAuditLink}</a>.
          ");

          $email->send();
        }
      }
    }
  }
}
