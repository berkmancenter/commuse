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

  public function getAllUniqueInterests()
  {
    $query = $this->db->query('SELECT DISTINCT jsonb_array_elements_text(interested_in) AS unique_interests FROM people;');
    $results = $query->getResultArray();

    $uniqueInterests = array_column($results, 'unique_interests');

    return $uniqueInterests;
  }
}
