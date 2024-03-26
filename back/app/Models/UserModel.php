<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;
use CodeIgniter\Shield\Traits\Resettable as Resettable;
use App\Libraries\UserProfileStructure;
use \Gumlet\ImageResize;

class UserModel extends ShieldUserModel
{
  use Resettable;

  const BASE_FIELDS = [
    'first_name', 'middle_name', 'last_name', 'bio', 'public_profile', 'email',
    'prefix', 'preferred_pronouns', 'mobile_phone_number',
  ];

  protected function initialize(): void
  {
    parent::initialize();
  }

  public function getUserProfileData($id)
  {
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
          $value = json_decode($customFieldRecord['value_json']);
        }

        if ($customFieldRecord['input_type'] === 'multi') {
          $multiValues = [];
          $fieldMetadata = json_decode($customFieldRecord['metadata'], true);
          $childFieldsIds = array_map(fn($childField) => $childField['id'], $fieldMetadata['childFields']);
          $customFieldsModel = model('CustomFieldModel');
          $customFields = $customFieldsModel
            ->select('id, machine_name')
            ->whereIn('id', $childFieldsIds)
            ->findAll();

          foreach ($customFields as $customField) {
            $childFieldValues = array_filter($customFieldsData, fn($customFieldData) => $customFieldData['machine_name'] === $customField['machine_name'] && $customFieldData['parent_field_value_index'] !== null);

            foreach ($childFieldValues as $childFieldValue) {
              if (isset($multiValues[$childFieldValue['parent_field_value_index']]) === false) {
                $multiValues[$childFieldValue['parent_field_value_index']] = [];
              }

              $multiValues[$childFieldValue['parent_field_value_index']][$childFieldValue['machine_name']] = $childFieldValue['value'];
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

  public function getUserProfileStructure()
  {
    $userProfileStructure = new UserProfileStructure();
    return $userProfileStructure->getUserProfileStructure();
  }

  public function saveProfileData($requestData, $userId = null) {
    $peopleModel = new PeopleModel();
    if (is_null($userId)) {
      $userId = auth()->id();
    }

    $cache = \Config\Services::cache();
    $cache->delete("person_{$userId}");
    $cache->delete('filters_with_values');
    $cachePeopleSearchPath = ROOTPATH . 'writable/cache/people_*';
    exec("rm {$cachePeopleSearchPath}");

    $existingPerson = $peopleModel->where('user_id', $userId)->first();

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

    $message = $existingPerson ? 'Profile updated successfully' : 'Profile created successfully';

    return [$this->db->transStatus(), $message];
  }

  private function saveCustomFieldsProfileData($requestData, $userId, $personBasicData) {
    // Let's try multiple times, the geocode API is limited to 1 request per second
    for ($try = 1; $try <= 5; $try++) {
      try {
        if ($requestData['current_city']) {
          $geoApiKey = $_ENV['geocode_maps_co_api.key'];
          $geoQuery = "{$requestData['current_city']},{$requestData['current_state']},{$requestData['current_country']}";
          $geoApiResponse = json_decode(file_get_contents("https://geocode.maps.co/search?q={$geoQuery}&api_key={$geoApiKey}"), true);
    
          if (count($geoApiResponse) > 0) {
            $requestData['current_location_lat'] = $geoApiResponse[0]['lat'];
            $requestData['current_location_lon'] = $geoApiResponse[0]['lon'];
            break;
          }
        }
      } catch (\Throwable $exception) {
        error_log($exception->getMessage());
      }
    
      // If it's not the last try, wait before the next attempt
      if ($try < 5) {
        sleep(2);
      }
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
              ->select('id, machine_name')
              ->whereIn('id', $childFieldsIds)
              ->findAll();

            $db
              ->table('custom_field_data')
              ->whereIn('custom_field_id', $childFieldsIds)
              ->where('model_id', $userId)
              ->delete();

            foreach ($value as $index => $itemData) {
              foreach ($itemData as $machineName => $itemDataValue) {
                $itemFieldId = current(array_filter($customFields, fn($customFieldsItem) => $customFieldsItem['machine_name'] === $machineName));

                if (!isset($itemFieldId['id'])) {
                  continue;
                }

                $childFieldsData[] = [
                  'custom_field_id' => $itemFieldId['id'],
                  'model_id' => $userId,
                  'parent_field_value_index' => $index,
                  'value' => $itemDataValue,
                  'value_json' => '[]',
                ];
              }
            }

            if (!empty($childFieldsData)) {
              $builder = $db->table('custom_field_data');
              $result = $builder
                ->onConstraint(['custom_field_id', 'model_id', 'parent_field_value_index'])
                ->upsertBatch($childFieldsData);

              $fieldData['value'] = '';
              $fieldData['value_json'] = '[true]';
            } else {
              $fieldData['value'] = '';
              $fieldData['value_json'] = '[]';
            }
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

  private function downloadRemoteImage($remoteURL, $localDirectory)
  {
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
}
