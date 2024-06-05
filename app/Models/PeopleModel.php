<?php

namespace App\Models;

use CodeIgniter\Model;

class PeopleModel extends Model
{
  protected $DBGroup      = 'default';
  protected $table      = 'people';
  protected $primaryKey     = 'id';
  protected $useAutoIncrement = true;
  protected $returnType     = 'array';
  protected $useSoftDeletes   = false;
  protected $protectFields  = false;
  protected $allowedFields  = [];

  // Dates
  protected $useTimestamps = true;
  protected $dateFormat  = 'datetime';
  protected $createdField  = 'created_at';
  protected $updatedField  = 'updated_at';
  protected $deletedField  = 'deleted_at';

  // Validation
  protected $validationRules    = [];
  protected $validationMessages   = [];
  protected $skipValidation     = false;
  protected $cleanValidationRules = true;

  // Callbacks
  protected $allowCallbacks = true;
  protected $beforeInsert   = [];
  protected $afterInsert  = [];
  protected $beforeUpdate   = [];
  protected $afterUpdate  = [];
  protected $beforeFind   = [];
  protected $afterFind    = [];
  protected $beforeDelete   = [];
  protected $afterDelete  = [];

  /**
   * Get people with custom fields.
   *
   * @param array $extraConditions
   * @param array $likeConditions
   * @param array $filters
   * @return array
   */
  public function getPeopleWithCustomFields(
    array $extraConditions = [],
    array $likeConditions = [],
    array $filters = []
  ): array {
    $people = $this->getPeople($extraConditions, $likeConditions, $filters);
    $this->processData($people);

    return $people;
  }

  /**
   * Get people with custom fields.
   *
   * @param array $extraConditions
   * @param array $likeConditions
   * @param array $filters
   * @return array
   */
  private function getPeople(
    array $extraConditions,
    array $likeConditions,
    array $filters
  ): array {
    $builder = $this->db->table('people');

    $builder
      ->select('
        people.id,
        people.first_name,
        people.middle_name,
        people.last_name,
        people.email,
        people.mobile_phone_number,
        people.image_url,
        people.bio,
        people.preferred_pronouns,
        people.user_id,
        people.public_profile,
        people.updated_at,
        json_agg(
          json_build_object(
            \'input_type\',
            custom_fields.input_type,
            \'value\',
            custom_field_data.value,
            \'value_json\',
            custom_field_data.value_json,
            \'machine_name\',
            custom_fields.machine_name,
            \'parent_field_value_index\',
            custom_field_data.parent_field_value_index
          )
        ) AS custom_fields
      ')
      ->join('custom_field_data', 'custom_field_data.model_id = people.id', 'left')
      ->join('custom_fields', 'custom_fields.id = custom_field_data.custom_field_id', 'left')
      ->join('users', 'users.id = people.user_id', 'left')
      ->where('custom_fields.model_name', 'People')
      ->where('users.id IS NOT NULL');

    if (!empty($extraConditions)) {
      $builder->where($extraConditions);
    }

    if (!empty($likeConditions)) {
      $builder->like($likeConditions);
    }

    $builder->groupBy('people.id');
    $this->applyFilters($builder, $filters);

    $people = $builder->get()->getResultArray();

    return $people;
  }

  /**
   * Apply filters to the query builder.
   *
   * @param object $builder
   * @param array $filters
   * @return void
   */
  private function applyFilters($builder, array $filters) {
    if (empty($filters)) {
      return;
    }

    $anyWithValues = false;
    foreach ($filters as $filterValues) {
      if (!empty($filterValues)) {
        $anyWithValues = true;
        break;
      }
    }

    if ($anyWithValues === false) {
      return;
    }

    $filtersMachineNames = array_keys($filters);
    $dbFields = $this->db->table('custom_fields')
      ->whereIn('machine_name', $filtersMachineNames)
      ->get()->getResultArray();

    $havingFilters = [];
    foreach ($filters as $filterKey => $filterValues) {
      $fieldDb = current(
        array_filter($dbFields, function ($dbField) use ($filterKey) {
          return $dbField['machine_name'] === $filterKey;
        })
      );

      if ($fieldDb === false || empty($filterValues)) {
        continue;
      }

      $jsonValues = json_encode($filterValues);

      switch ($fieldDb['input_type']) {
        case 'short_text':
          $joinedInValues = implode(', ', array_map(function($filterValue) { return "'$filterValue'"; }, $filterValues));
          $havingFilters[] = "MAX(CASE WHEN \"custom_fields\".\"machine_name\" = '{$filterKey}' AND custom_field_data.value ILIKE any(array[({$joinedInValues})]) THEN 1 ELSE 0 END) = 1";
          break;
        case 'tags':
          $havingFilters[] = "bool_and( CASE WHEN \"custom_fields\".\"machine_name\" = '{$filterKey}' THEN lower(custom_field_data.value_json::text)::jsonb @> lower('{$jsonValues}')::jsonb END)";
          break;
        case 'tags_range':
          $tagHavingFilterStart = "WHEN \"custom_fields\".\"machine_name\" = '{$filterKey}' THEN";
          $tagHavingFilterParts = [];
          foreach ($filterValues as $value) {
            $tagHavingFilterParts[] = "
              EXISTS (
                SELECT 1
                FROM jsonb_array_elements(custom_field_data.value_json) AS elem
                WHERE (lower(elem::text)::jsonb)->'tags' @> lower('[\"{$value}\"]')::jsonb
            )";
          }
          $tagHavingFilterPartsTogether = implode(' AND ', $tagHavingFilterParts);
          $havingFilters[] = "bool_and( CASE {$tagHavingFilterStart} {$tagHavingFilterPartsTogether} END)";
          break;
      }
    }

    if (empty($havingFilters) === false) {
      $havingFiltersJoined = join(' AND ', $havingFilters);
      $builder->having($havingFiltersJoined);
    }
  }

  /**
   * Process people data.
   *
   * @param array $people
   * @return void
   */
  private function processData(array &$people) {
    foreach ($people as &$personData) {
      $personData['custom_fields'] = json_decode($personData['custom_fields'], true);

      foreach ($personData['custom_fields'] as &$customField) {
        $value = $customField['value'];

        if (in_array($customField['input_type'], ['tags_range', 'tags'])) {
          $value = $customField['value_json'];
        }

        if ($customField['input_type'] === 'long_text') {
          $value = nl2br($value);
        }

        $personData[$customField['machine_name']] = $value;
      }

      $personData['image_url'] = $personData['image_url'] ? "profile_images/{$personData['image_url']}" : '';
      $personData['bio'] = nl2br($personData['bio']);
    }
  }
}
