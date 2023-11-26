<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;
use CodeIgniter\Shield\Traits\Resettable as Resettable;

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
      ->where('people.id', $id)
      ->get()
      ->getRowArray();

    if ($baseUserData === null) {
      return [];
    }

    if ($baseUserData !== null) {
      $userData = array_merge($userData, $baseUserData);

      $customFieldsData = $builder
        ->select('custom_field_data.value, custom_field_data.value_json, custom_fields.machine_name, custom_fields.input_type')
        ->join('custom_field_data', 'custom_field_data.model_id = people.id', 'left')
        ->join('custom_fields', 'custom_fields.id = custom_field_data.custom_field_id', 'left')
        ->where('custom_fields.model_name = \'People\'')
        ->where('people.id', $id)
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
    $db = \Config\Database::connect();
    $builder = $db->table('custom_field_groups');

    $userProfileStructure = $builder
      ->select('
        custom_field_groups.machine_name AS group_machine_name,
        custom_field_groups.title AS group_title,
        custom_field_groups.description AS group_description,
        custom_fields.machine_name AS field_machine_name,
        custom_fields.title AS field_title,
        custom_fields.description AS field_description,
        custom_fields.metadata AS field_metadata,
        custom_fields.input_type AS field_input_type
      ')
      ->join('custom_fields', 'custom_fields.group_id = custom_field_groups.id AND custom_fields.model_name = \'People\'', 'left')
      ->orderBy('custom_field_groups.order ASC, custom_fields.order ASC')
      ->get()
      ->getResultArray();

    $customFieldGroups = [];

    foreach ($userProfileStructure as $row) {
      if (!isset($customFieldGroups[$row['group_machine_name']])) {
          $customFieldGroups[$row['group_machine_name']] = [
            'machine_name' => $row['group_machine_name'],
            'title' => $row['group_title'],
            'description' => $row['group_description'],
            'custom_fields' => [],
          ];
      }

      if ($row['field_machine_name']) {
        $customFieldGroups[$row['group_machine_name']]['custom_fields'][] = [
          'machine_name' => $row['field_machine_name'],
          'title' => $row['field_title'],
          'description' => $row['field_description'],
          'metadata' => json_decode($row['field_metadata'], true),
          'input_type' => $row['field_input_type'],
        ];
      }
    }

    $customFieldGroups = array_values($customFieldGroups);

    return $customFieldGroups;
  }

  public function saveProfileData($requestData) {
    $peopleModel = new PeopleModel();
    $userId = auth()->id();

    $existingPerson = $peopleModel->where('user_id', $userId)->first();

    $mappedData = [];
    foreach (UserModel::BASE_FIELDS as $key) {
      $mappedData[$key] = $requestData[$key] ?? '';
    }
    $data = $mappedData;

    $data['public_profile'] = $requestData['public_profile'] ?? false;
    $data['user_id'] = $userId;

    $peopleModel->db->transStart();
    if ($existingPerson) {
      $peopleModel->update($existingPerson['id'], $data);
    } else {
      $peopleModel->insert($data);
    }
    $this->saveCustomFieldsProfileData($requestData, $userId);
    $peopleModel->db->transComplete();

    $message = $existingPerson ? 'Profile updated successfully' : 'Profile created successfully';

    return [$this->db->transStatus(), $message];
  }

  private function saveCustomFieldsProfileData($requestData, $userId) {
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

      $fieldData = [
        'custom_field_id' => $customFieldToProcess['id'],
        'model_id' => $userId,
      ];

      if (in_array($customFieldToProcess['input_type'], ['tags_range', 'tags'])) {
        $fieldData['value_json'] = json_encode($value);
        $fieldData['value'] = '';
      } else {
        $fieldData['value'] = $value;
        $fieldData['value_json'] = '[]';
      }

      $customFieldsData[] = $fieldData;
    }

    $builder = $db->table('custom_field_data');
    $result = $builder
      ->onConstraint(['custom_field_id', 'model_id'])
      ->upsertBatch($customFieldsData);

    return $result;
  }
}
