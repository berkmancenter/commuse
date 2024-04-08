<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

class CustomFields extends BaseController
{
  use ResponseTrait;

  public function index()
  {
    $this->checkAdminAccess();

    $customFieldsModel = model('CustomFieldModel');

    $customFields = $customFieldsModel
      ->orderBy('title', 'asc')
      ->findAll();

    $customFields = array_map(function ($customField) {
      $customField['metadata'] = json_decode($customField['metadata']);

      if (isset($customField['metadata']->possibleValues)) {
        $customField['metadata']->possibleValues = join("\n", $customField['metadata']->possibleValues);
      }

      return $customField;
    }, $customFields);

    return $this->respond($customFields);
  }

  public function upsert()
  {
    $this->checkAdminAccess();

    $result = false;
    $customFieldsModel = model('CustomFieldModel');
    $requestData = json_decode(file_get_contents('php://input'), true);

    if (isset($requestData['customField']) === false) {
      return $this->respond(['message' => 'Error saving data.'], 421);
    }

    $requestData = $requestData['customField'];

    if (isset($requestData['metadata']['childFields']) && count($requestData['metadata']['childFields']) > 0) {
      $requestData['metadata']['childFields'] = array_map(fn($item) => [
        'id' => $item['id'],
        'title' => $item['title'],
      ], $requestData['metadata']['childFields']);
    }

    $data = [
      'title' => $requestData['title'],
      'metadata' => json_encode([
        'isLink'=> $requestData['metadata']['isLink'] ?? false,
        'hideTitle'=> $requestData['metadata']['hideTitle'] ?? false,
        'allowMultiple'=> $requestData['metadata']['allowMultiple'] ?? false,
        'allowNewValues'=> $requestData['metadata']['allowNewValues'] ?? false,
        'shareUserValues'=> $requestData['metadata']['shareUserValues'] ?? false,
        'isImportProfileImageLink'=> $requestData['metadata']['isImportProfileImageLink'] ?? false,
        'isPeopleFilter'=> $requestData['metadata']['isPeopleFilter'] ?? false,
        'possibleValues'=> preg_split('/\R/u', $requestData['metadata']['possibleValues'] ?? ''),
        'tagName' => $requestData['metadata']['tagName'] ?? '',
        'childFields' => $requestData['metadata']['childFields'] ?? [],
      ]),
    ];

    $id = $requestData['id'] ?? null;

    if (isset($requestData['metadata']['childFields']) && count($requestData['metadata']['childFields']) > 0) {
      $childrenIds = array_map(fn($item) => $item['id'], $requestData['metadata']['childFields']);
      $customFieldsModelChildren = model('CustomFieldModel');
      $customFieldsModelChildren
        ->set('parent_field_id', $id)
        ->whereIn('id', $childrenIds)
        ->update();
    }

    if ($id) {
      $existingCustomField = $customFieldsModel->where('id', $id)->first();

      if ($existingCustomField) {
        $message = 'Custom field has been updated successfully.';
        $result = $customFieldsModel->update($existingCustomField['id'], $data);
      }
    }

    if ($result) {
      $cache = \Config\Services::cache();
      $cache->delete('filters_with_values');

      return $this->respond(['message' => $message], 200);
    } else {
      return $this->respond(['message' => 'Error saving data.'], 500);
    }
  }
}
