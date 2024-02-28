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

  public function getPeopleWithCustomFields(
    array $extraConditions = [],
    array $likeConditions = [],
    array $filters = []
  ): array {
    $people = $this->getPeople($extraConditions, $likeConditions, $filters);
    $this->processData($people);

    return $people;
  }

  private function getPeople(
    array $extraConditions,
    array $likeConditions,
    array $filters
  ) {
    $builder = $this->db->table('people');

    $builder
      ->select('
        people.id,
        people.first_name,
        people.middle_name,
        people.last_name,
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
            custom_fields.machine_name
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

    $builder->groupBy('people.id, custom_field_data.model_id');
    $this->applyFilters($builder, $filters);

    $people = $builder->get()->getResultArray();

    return $people;
  }

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

    $havingFilters = [];
    foreach ($filters as $filterKey => $filterValues) {
      if (empty($filterValues)) {
        continue;
      }

      $jsonValues = json_encode($filterValues);
      $havingFilters[] = "WHEN \"custom_fields\".\"machine_name\" = '{$filterKey}' THEN custom_field_data.value_json @> '{$jsonValues}' ";
    }

    if (empty($havingFilters) === false) {
      $havingFiltersJoined = join(' ', $havingFilters);

      $having = <<<EOD
        bool_and(
          CASE
            $havingFiltersJoined
          END
        )
      EOD;

      $builder->having($having);
    }
  }

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
