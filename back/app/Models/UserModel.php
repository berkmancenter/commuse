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
        ->select('custom_field_data.value, custom_field_data.value_json, custom_fields.machine_name, custom_fields.input_type')
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
    $peopleModel = new PeopleModel();
    $dataKeys = array_keys($requestData);
    $db = \Config\Database::connect();
    $builder = $db->table('custom_fields');

    $customFieldsToProcess = $builder
      ->select('
        custom_fields.*
      ')
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

      if (in_array($customFieldToProcess['input_type'], ['tags_range', 'tags'])) {
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

        $fieldData['value_json'] = json_encode($value);
        $fieldData['value'] = '';
      } else {
        $fieldData['value'] = strip_tags($value);
        $fieldData['value_json'] = '[]';
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
        ->onConstraint(['custom_field_id', 'model_id'])
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
