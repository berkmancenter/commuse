<?php

namespace App\Libraries;

use Stringy\Stringy as S;

class UserProfileStructure {
  private $db;

  public function __construct() {
    $this->db = \Config\Database::connect();
  }

  public function getUserProfileStructure() {
    $userProfileData = $this->getUserProfileGroupsAndFields();

    return $this->formatUserProfileStructure($userProfileData);
  }

  public function getFiltersWithValues() {
    $cache = \Config\Services::cache();
    $cachedData = $cache->get('filters_with_values');

    if ($cachedData && Cache::isCacheEnabled()) {
      return $cachedData;
    }

    $userProfileTagFields = $this->getUserProfileGroupsAndFields([
      "custom_fields.metadata->>'isPeopleFilter' = 'true'"
    ], ['tags', 'tags_range', 'short_text']);

    foreach ($userProfileTagFields as &$userProfileTagField) {
      $userProfileTagField['values'] = $this->getFieldUserValues($userProfileTagField['field_id'], []);
    }

    usort($userProfileTagFields, function($a, $b) {
      return $a['field_title'] <=> $b['field_title'];
    });

    $cache->save('filters_with_values', $userProfileTagFields, Cache::$defaultCacheExpiration);

    return $userProfileTagFields;
  }

  private function getUserProfileGroupsAndFields($additionalConditions = [], $fieldTypes = []) {
    $builder = $this->db->table('custom_field_groups');

    $groupsAndFieldsQuery = $builder
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
      ->orderBy('custom_field_groups.order ASC, custom_fields.order ASC');

    foreach ($additionalConditions as $additionalCondition) {
      $builder->where($additionalCondition, null, false);
    }

    if (empty($fieldTypes) === false) {
      $groupsAndFieldsQuery->whereIn('custom_fields.input_type', $fieldTypes);
    }

    return $groupsAndFieldsQuery
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

    if (isset($metadata['possibleValues']) === false) {
      $metadata['possibleValues'] = [];
    }

    $childFieldsConfig = [];
    if (isset($metadata['childFields']) && count($metadata['childFields']) > 0) {
      $builder = $this->db->table('custom_fields');
      $childFieldsIds = array_map(fn($item) => $item['id'], $metadata['childFields']);
      $childFieldsConfig = $builder
        ->select('
          custom_fields.machine_name AS field_machine_name,
          custom_fields.id AS field_id,
          custom_fields.title AS field_title,
          custom_fields.description AS field_description,
          custom_fields.metadata AS field_metadata,
          custom_fields.input_type AS field_input_type
        ')
        ->whereIn('id', $childFieldsIds)
        ->orderBy('order')
        ->get()
        ->getResultArray();

      usort($childFieldsConfig, function($a, $b) use ($childFieldsIds) {
        $index_a = array_search($a['field_id'], $childFieldsIds);
        $index_b = array_search($b['field_id'], $childFieldsIds);
        return $index_a - $index_b;
      });

      $childFieldsConfig = array_map(function($childField) {
        $childField = $this->processField($childField);

        return $childField;
      }, $childFieldsConfig);
    }

    return [
      'machine_name' => $row['field_machine_name'],
      'title' => $row['field_title'],
      'description' => $row['field_description'],
      'metadata' => $metadata,
      'input_type' => $row['field_input_type'],
      'child_fields' => $childFieldsConfig,
    ];
  }

  private function getFieldUserValues($fieldId, $existingValues = []) {
    $builder = $this->db->table('custom_field_data');
    $fieldUserValues = $builder
      ->select('
        custom_field_data.value,
        custom_field_data.value_json,
        custom_fields.input_type
      ')
      ->join('custom_fields', 'custom_fields.id = custom_field_data.custom_field_id', 'left')
      ->join('people', 'people.id = custom_field_data.model_id')
      ->where('custom_fields.id', $fieldId)
      ->where('people.public_profile', true)
      ->get()
      ->getResultArray();

    foreach ($fieldUserValues as $fieldUserValue) {
      switch ($fieldUserValue['input_type']) {
        case 'short_text':
          $values = [$fieldUserValue['value']];
          break;
        case 'tags':
          $values = json_decode($fieldUserValue['value_json'], true);
          break;
        case 'tags_range':
          $rangeData = json_decode($fieldUserValue['value_json'], true);
          $values = [];

          foreach ($rangeData as $rangeDataItem) {
            if (isset($rangeDataItem['tags']) && is_array($rangeDataItem['tags']) === true) {
              $values = array_merge($values, $rangeDataItem['tags']);
            }
          }
          break;
      }

      # FIXME This shouldn't be possible to have a string here, just a quick fix
      # until we figure out how it's possible to enter a string here into the db
      if (is_string($values)) {
        $values = [$values];
      }

      if (!empty($values)) {
        $existingValues = array_merge($existingValues, $values);
      }
    }

    $existingValues = array_map('strtolower', $existingValues);
    $existingValues = array_filter($existingValues);
    $existingValues = array_map('trim', $existingValues);
    $existingValues = array_unique($existingValues);
    $existingValues = array_map(function($string) {
      return S::create($string)->titleizeForHumans();
    }, $existingValues);
    asort($existingValues);
    $existingValues = array_values($existingValues);

    return $existingValues;
  }
}
