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
class People extends BaseController
{
  use ResponseTrait;

  /**
   * Retrieves a list of people based on the provided query and filters.
   *
   * @return \CodeIgniter\HTTP\Response
   */
  public function index()
  {
    $requestData = json_decode(file_get_contents('php://input'), true);
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

    $people = $peopleModel->getPeopleWithCustomFields(
      [
        'people.public_profile' => true,
      ],
      [
        'people.full_text_search' => strtolower($requestData['q']),
      ],
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
}
