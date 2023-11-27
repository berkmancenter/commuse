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

  public function getPeopleWithCustomFields($extraConditions = [])
  {
    $db = \Config\Database::connect();
    $builder = $db->table('people');
    $usersData = [];

    $baseUsersData = $builder
      ->select('people.*')
      ->where($extraConditions)
      ->get()
      ->getResultArray();

    $usersData = array_merge($usersData, $baseUsersData);

    $customFieldsData = $builder
      ->select('
        custom_field_data.model_id,
        custom_field_data.value,
        custom_field_data.value_json,
        custom_fields.machine_name,
        custom_fields.input_type
      ')
      ->join('custom_field_data', 'custom_field_data.model_id = people.id', 'left')
      ->join('custom_fields', 'custom_fields.id = custom_field_data.custom_field_id', 'left')
      ->where('custom_fields.model_name = \'People\'')
      ->where($extraConditions)
      ->get()
      ->getResultArray();

      foreach ($usersData as &$userData) {
        $matchingFields = array_values(array_filter($customFieldsData, function ($customFieldData) use ($userData) {
          return $customFieldData['model_id'] === $userData['id'];
        }));

      if (!empty($matchingFields)) {
        $userCustomFields = [];
        foreach ($matchingFields as $customFieldRecord) {
          $value = $customFieldRecord['value'];
          if (in_array($customFieldRecord['input_type'], ['tags_range', 'tags'])) {
            $value = json_decode($customFieldRecord['value_json']);
          }

          $userCustomFields[$customFieldRecord['machine_name']] = $value;
        }

        $userData = array_merge($userData, $userCustomFields);
      }

      $userData['image_url'] = $userData['image_url'] ? "profile_images/{$userData['image_url']}" : '';
    }

    return $usersData;
  }
}
