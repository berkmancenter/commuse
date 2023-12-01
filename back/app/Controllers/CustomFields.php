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

    $data = [
      'title' => $requestData['title'],
      'metadata' => json_encode([
        'isLink'=> $requestData['metadata']['isLink'] ?? false,
        'allowMultiple'=> $requestData['metadata']['allowMultiple'] ?? false,
        'allowNewValues'=> $requestData['metadata']['allowNewValues'] ?? false,
        'shareUserValues'=> $requestData['metadata']['shareUserValues'] ?? false,
        'possibleValues'=> preg_split('/\R/u', $requestData['metadata']['possibleValues'] ?? ''),
        'tagName' => $requestData['metadata']['tagName'] ?? '',
      ]),
    ];

    $id = $requestData['id'] ?? null;

    if ($id) {
      $existingCustomField = $customFieldsModel->where('id', $id)->first();

      if ($existingCustomField) {
        $message = 'Custom field has been updated successfully.';
        $result = $customFieldsModel->update($existingCustomField['id'], $data);
      }
    }

    if ($result) {
      return $this->respond(['message' => $message], 200);
    } else {
      return $this->respond(['message' => 'Error saving data.'], 500);
    }
  }
}
