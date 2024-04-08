<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

class DataEditor extends BaseController
{
  use ResponseTrait;

  public function index() {
    $this->checkAdminAccess();

    $db = \Config\Database::connect();
    $requestData = $this->request->getJSON(true);
    $query = $db->escapeLikeString(strtolower($requestData['q']));

    $builder = $db->table('custom_field_data cfd');

    $customFieldsData = $builder
      ->select('cfd.value, cfd.value_json, cfd.id, cfd.model_id, cf.model_name, cf.title AS field_title')
      ->join('custom_fields cf', 'cf.id = cfd.custom_field_id', 'left')
      ->where("LOWER(value) ILIKE '%" . $query . "%'")
      ->orWhere("LOWER(value_json::text) ILIKE '%" . $query . "%'")
      ->get()
      ->getResultArray();

    return $this->respond($customFieldsData);
  }

  public function saveItem() {
    $this->checkAdminAccess();

    $requestData = $this->request->getJSON(true);
    $db = \Config\Database::connect();

    $builder = $db->table('custom_field_data');
    $fieldData = $requestData;
    unset($fieldData['id']);
    unset($fieldData['model_name']);
    unset($fieldData['field_title']);
    $fieldData['updated_at'] = date('Y-m-d H:i:s');
    $builder->set($fieldData);
    $builder->where('id', $requestData['id']);
    $result = $builder->update();

    if ($requestData['model_name'] === 'People') {
      $cache = \Config\Services::cache();
      $cache->delete("person_{$requestData['model_id']}");
      $cache->delete('filters_with_values');
      $cachePeopleSearchPath = ROOTPATH . 'writable/cache/people_*';
      exec("rm {$cachePeopleSearchPath} > /dev/null 2> /dev/null");
    }

    if ($result) {
      return $this->respond(['message' => 'Data field has been set successfully.'], 200);
    } else {
      return $this->respond(['message' => 'Error changing user role.'], 500);
    }
  }
}
