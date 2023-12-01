<?php

namespace App\Libraries;

class UserProfileStructure {
  private $db;

  public function __construct() {
    $this->db = \Config\Database::connect();
  }

  public function getUserProfileStructure() {
    $userProfileData = $this->getUserProfileStructureData();

    return $this->formatUserProfileStructure($userProfileData);
  }

  private function getUserProfileStructureData() {
    $builder = $this->db->table('custom_field_groups');

    return $builder
      ->select('
        custom_field_groups.machine_name AS group_machine_name,
        custom_field_groups.title AS group_title,
        custom_field_groups.description AS group_description,
        custom_fields.machine_name AS field_machine_name,
        custom_fields.id AS field_id,
        custom_fields.title AS field_title,
        custom_fields.description AS field_description,
        custom_fields.metadata AS field_metadata,
        custom_fields.input_type AS field_input_type
      ')
      ->join('custom_fields', 'custom_fields.group_id = custom_field_groups.id AND custom_fields.model_name = \'People\'', 'left')
      ->orderBy('custom_field_groups.order ASC, custom_fields.order ASC')
      ->get()
      ->getResultArray();
  }

  private function formatUserProfileStructure($userProfileStructure) {
    $customFieldGroups = [];
    foreach ($userProfileStructure as $row) {
      $groupMachineName = $row['group_machine_name'];

      if (!isset($customFieldGroups[$groupMachineName])) {
        $customFieldGroups[$groupMachineName] = $this->initializeGroup($row);
      }

      if ($row['field_machine_name']) {
        $customFieldGroups[$groupMachineName]['custom_fields'][] = $this->processField($row);
      }
    }

    return array_values($customFieldGroups);
  }

  private function initializeGroup($row) {
    return [
      'machine_name' => $row['group_machine_name'],
      'title' => $row['group_title'],
      'description' => $row['group_description'],
      'custom_fields' => [],
    ];
  }

  private function processField($row) {
    $metadata = json_decode($row['field_metadata'], true);

    if (isset($metadata['shareUserValues']) && $metadata['shareUserValues'] === true) {
      $metadata['possibleValues'] = $this->getFieldUserValues($row['field_id'], $metadata['possibleValues']);
    }

    return [
      'machine_name' => $row['field_machine_name'],
      'title' => $row['field_title'],
      'description' => $row['field_description'],
      'metadata' => $metadata,
      'input_type' => $row['field_input_type'],
    ];
  }

  private function getFieldUserValues($fieldId, $existingValues) {
    $builder = $this->db->table('custom_field_data');
    $fieldUserValues = $builder
      ->select('value_json')
      ->join('custom_fields', 'custom_fields.id = custom_field_data.custom_field_id', 'left')
      ->where('custom_fields.id', $fieldId)
      ->get()
      ->getResultArray();

    foreach ($fieldUserValues as $fieldUserValue) {
      $values = json_decode($fieldUserValue['value_json'], true);

      if (!empty($values)) {
        $existingValues = array_merge($existingValues, $values);
      }
    }

    if (is_array($existingValues)) {
      $existingValues = array_unique($existingValues);
      asort($existingValues);
      $existingValues = array_values($existingValues);
    }


    return $existingValues;
  }
}
