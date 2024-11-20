<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\PeopleModel;
use App\Libraries\UserProfileStructure;
use App\Libraries\Cache;
use League\Csv\Writer;

/**
 * Controller for handling people-related operations.
 */
class PeopleController extends BaseController
{
  use ResponseTrait;

  /**
   * Retrieves a list of people based on the provided query and filters.
   *
   * @return \CodeIgniter\HTTP\Response
   */
  public function index()
  {
    $requestData = $this->request->getJSON(true);
    $query = $requestData['q'];
    $filters = $requestData['filters'];
    ksort($filters);
    $searchMd5 = md5(json_encode($filters) . $query);

    $cache = \Config\Services::cache();
    $cachedData = $cache->get("people_{$searchMd5}");

    if ($cachedData && Cache::isCacheEnabled()) {
      return $this->respond(json_decode($cachedData));
    }

    $peopleModel = new PeopleModel();

    $extraConditions = [
      'people.public_profile' => true,
      'users.status' => NULL,
    ];

    $whereInConditions = [];
    if ($query && strlen($query) > 1) {
      $elasticClient = service('elasticsearchClient');

      // Construct the base query for Elasticsearch
      $searchQuery = [
        'query' => [
          'bool' => [
            'must' => [
              'match' => [
                'search_content' => [
                  'query' => $query,
                  'operator' => 'and',
                ],
              ],
            ],
          ],
        ],
        'size' => 10000,
      ];

      // Execute the search query
      $results = $elasticClient->search($peopleModel->getSearchIndexName(), $searchQuery);
  
      // Check for errors in the Elasticsearch response
      if (isset($results['error'])) {
        return $this->fail($results['error']);
      }

      $peopleIds = array_map(function($result) {
        return $result['_source']['id'];
      }, $results['hits']['hits']);

      if (empty($peopleIds) === false) {
        $whereInConditions['people.id'] = $peopleIds;
      } else {
        $whereInConditions['people.id'] = [0];
      }
    }

    $people = $peopleModel->getPeopleWithCustomFields(
      $extraConditions,
      [],
      $whereInConditions,
      $requestData['filters'],
    );

    $cache->save("people_{$searchMd5}", json_encode($people), Cache::$defaultCacheExpiration);

    return $this->respond($people);
  }

  /**
   * Retrieves a specific person by their ID.
   *
   * @param int $id The ID of the person to retrieve.
   * @return \CodeIgniter\HTTP\Response
   */
  public function person($id)
  {
    $cache = \Config\Services::cache();
    $cachedData = $cache->get("person_{$id}");

    if ($cachedData && Cache::isCacheEnabled()) {
      return $this->respond($cachedData);
    }

    $peopleModel = new PeopleModel();

    if (auth()->user()->can('admin.access') === false) {
      $person = $peopleModel->getPeopleWithCustomFields([
        'people.id' => $id,
        'people.public_profile' => true,
      ]);
    } else {
      $person = $peopleModel->getPeopleWithCustomFields([
        'people.id' => $id,
      ]);
    }

    if (empty($person)) {
      return $this->respond(['message' => 'Person not found.'], 404);
    }

    $cache->save("person_{$id}", $person[0], Cache::$defaultCacheExpiration);

    return $this->respond($person[0]);
  }

  /**
   * Retrieves the available filters with their values.
   *
   * @return \CodeIgniter\HTTP\Response
   */
  public function filters()
  {
    $userProfileStructure = new UserProfileStructure();

    $filters = $userProfileStructure->getFiltersWithValues();

    return $this->respond($filters);
  }

  /**
   * Exports a list of people in the specified format.
   *
   * @return \CodeIgniter\HTTP\Response
   */
  public function export()
  {
    $requestedFormat = $this->request->getGet('format');
    $listOfIds = $this->request->getGet('ids');
    $idsArray = explode(',', $listOfIds);

    if (empty($idsArray) === true) {
      return $this->respond('Users list is empty.');
    }

    switch ($requestedFormat) {
      case 'csv':
        $result = $this->getExportedCsv($idsArray);
        $formatted_datetime = date('Y-m-d_H-m-s');
        $filename = "exported_people_{$formatted_datetime}.csv";

        return $this->response->download($filename, $result);
    }

    return $this->respond(['message' => 'Format not found.']);
  }

  /**
   * Generates an exported CSV file with a list of people for the given list of IDs.
   *
   * @param array $idsArray The array of IDs to export.
   * @return string The generated CSV file as a string.
   */
  private function getExportedCsv($idsArray = []) {
    $peopleModel = new PeopleModel();

    $people = $peopleModel
      ->select('first_name, last_name, email')
      ->whereIn('id', $idsArray)
      ->orderBy('last_name', 'asc')
      ->findAll();

    $csv = Writer::createFromFileObject(new \SplTempFileObject());
    $csv->insertOne(['firstname', 'lastname', 'email']);
    $csv->insertAll($people);

    return $csv->toString();
  }

  public function exportAllData()
  {
    $this->checkAdminAccess();

    $requestedFormat = $this->request->getGet('format');
    $listOfIds = $this->request->getGet('ids');
    $idsArray = explode(',', $listOfIds);

    if (empty($idsArray)) {
      return $this->respond('Users list is empty.');
    }

    switch ($requestedFormat) {
      case 'csv':
        $result = $this->getExportedCsvAllData($idsArray);
        $formatted_datetime = date('Y-m-d_H-m-s');
        $filename = "exported_people_{$formatted_datetime}.csv";

        return $this->response->download($filename, $result);
    }

    return $this->respond(['message' => 'Format not found.']);
  }

  private function getExportedCsvAllData(array $idsArray = [])
  {
    $peopleModel = new PeopleModel();
    $people = $peopleModel->getPeopleWithCustomFields(['people.id' => $idsArray]);
  
    if (empty($people)) {
      return '';
    }

    foreach ($people as &$person) {
      foreach ($person as $key => $value) {
        if (is_array($value)) {
          $person[$key] = $this->flattenArrayToString($value);
        }
      }
    }

    // Dynamically derive CSV headers from the first person's keys
    $csvHeaders = array_keys($people[0]);

    $csv = Writer::createFromFileObject(new \SplTempFileObject());
    $csv->insertOne($csvHeaders);

    foreach ($people as $person) {
      // Reorder the fields in each person’s data to match the header order
      $orderedPerson = [];
      foreach ($csvHeaders as $header) {
        // Ensure the ordered array has all keys even if some are missing in person
        $orderedPerson[] = isset($person[$header]) ? $person[$header] : '';
      }
      $csv->insertOne($orderedPerson);
    }

    return $csv->toString();
  }

  private function flattenArrayToString($array, $separator = ', ') {
    $result = [];
    foreach ($array as $element) {
      if (is_array($element)) {
          $result[] = $this->flattenArrayToString($element, $separator);
      } else {
          $result[] = $element;
      }
    }

    return implode($separator, $result);
  }
}
