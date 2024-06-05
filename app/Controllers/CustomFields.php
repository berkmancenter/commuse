<?php
namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

class CustomFields extends BaseController
{
  use ResponseTrait;

  /**
   * Retrieves all custom fields.
   *
   * @return \CodeIgniter\HTTP\Response
   */
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

  /**
  * Upserts a custom field.
  *
  * This method handles the creation or updating of a custom field based on the provided request data.
  * It checks if the user has admin access, validates the request data, prepares the custom field data,
  * updates any child fields if present, and updates the existing custom field if an ID is provided.
  * If the update is successful, it deletes the 'filters_with_values' cache and responds with a success message.
  * If the update fails, it responds with an error message.
  *
  * @return \CodeIgniter\HTTP\Response
  */
  public function upsert()
  {
    $this->checkAdminAccess();

    $customFieldsModel = model('CustomFieldModel');
    $requestData = $this->request->getJSON(true);

    if (!isset($requestData['customField'])) {
      return $this->respond(['message' => 'Error saving data.'], 400);
    }

    $customFieldData = $requestData['customField'];
    $data = $this->prepareCustomFieldData($customFieldData);
    $id = $customFieldData['id'] ?? null;
    $this->updateChildFields($customFieldData, $id);
    $result = $this->updateCustomField($customFieldsModel, $id, $data);

    if ($result) {
      $cache = \Config\Services::cache();
      $cache->delete('filters_with_values');

      return $this->respond(['message' => 'Custom field has been updated successfully.'], 200);
    } else {
      return $this->respond(['message' => 'Error saving data.'], 500);
    }
  }

  /**
  * Prepares the custom field data for saving.
  *
  * This method takes the custom field data from the request and prepares it for saving.
  * It maps the child fields to include only the 'id' and 'title' properties if they exist.
  * It then returns an array containing the prepared custom field data.
  *
  * @param array $customFieldData The custom field data from the request.
  * @return array The prepared custom field data ready for saving.
  */
  private function prepareCustomFieldData($customFieldData)
  {
    if (isset($customFieldData['metadata']['childFields']) && count($customFieldData['metadata']['childFields'])) {
      $customFieldData['metadata']['childFields'] = array_map(fn($item) => [
        'id' => $item['id'],
        'title' => $item['title'],
      ], $customFieldData['metadata']['childFields']);
    }

    return [
      'title' => $customFieldData['title'],
      'metadata' => json_encode([
        'isLink' => $customFieldData['metadata']['isLink'] ?? false,
        'hideTitle' => $customFieldData['metadata']['hideTitle'] ?? false,
        'allowMultiple' => $customFieldData['metadata']['allowMultiple'] ?? false,
        'allowNewValues' => $customFieldData['metadata']['allowNewValues'] ?? false,
        'shareUserValues' => $customFieldData['metadata']['shareUserValues'] ?? false,
        'isImportProfileImageLink' => $customFieldData['metadata']['isImportProfileImageLink'] ?? false,
        'isPeopleFilter' => $customFieldData['metadata']['isPeopleFilter'] ?? false,
        'possibleValues' => preg_split('/\R/u', $customFieldData['metadata']['possibleValues'] ?? ''),
        'tagName' => $customFieldData['metadata']['tagName'] ?? '',
        'childFields' => $customFieldData['metadata']['childFields'] ?? [],
      ]),
    ];
  }

  /**
  * Updates the parent_field_id of child fields.
  *
  * This method updates the parent_field_id of child fields if they exist in the custom field data.
  * It retrieves the IDs of the child fields and updates their parent_field_id to the provided parent ID.
  *
  * @param array $customFieldData The custom field data from the request.
  * @param int|null $parentId The ID of the parent custom field.
  * @return void
  */
  private function updateChildFields($customFieldData, $parentId)
  {
    if (isset($customFieldData['metadata']['childFields']) && count($customFieldData['metadata']['childFields'])) {
      $childrenIds = array_column($customFieldData['metadata']['childFields'], 'id');
      $customFieldsModelChildren = model('CustomFieldModel');
      $customFieldsModelChildren
        ->set('parent_field_id', $parentId)
        ->whereIn('id', $childrenIds)
        ->update();
    }
  }

  /**
  * Updates an existing custom field.
  *
  * This method updates an existing custom field in the database if an ID is provided.
  * It retrieves the existing custom field based on the provided ID and updates its data.
  *
  * @param object $customFieldsModel The instance of the CustomFieldModel.
  * @param int|null $id The ID of the custom field to update.
  * @param array $data The data to update the custom field with.
  * @return bool Returns true if the update is successful, false otherwise.
  */
  private function updateCustomField($customFieldsModel, $id, $data)
  {
    if ($id) {
      $existingCustomField = $customFieldsModel->where('id', $id)->first();
      if ($existingCustomField) {
        return $customFieldsModel->update($existingCustomField['id'], $data);
      }
    }

    return false;
  }
}
