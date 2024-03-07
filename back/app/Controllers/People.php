<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\PeopleModel;
use App\Libraries\UserProfileStructure;
use League\Csv\Writer;

class People extends BaseController
{
  use ResponseTrait;

  public function index()
  {
    $peopleModel = new PeopleModel();
    $requestData = json_decode(file_get_contents('php://input'), true);

    $people = $peopleModel->getPeopleWithCustomFields(
      [
        'people.public_profile' => true,
      ],
      [
        'people.full_text_search' => strtolower($requestData['q']),
      ],
      $requestData['filters'],
    );

    return $this->respond($people);
  }

  public function person($id)
  {
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

    return $this->respond($person[0]);
  }

  public function filters()
  {
    $userProfileStructure = new UserProfileStructure();

    $filters = $userProfileStructure->getFiltersWithValues();

    return $this->respond($filters);
  }

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
        break;
    }

    return $this->respond(['message' => 'Format not found.']);
  }

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

    return $csv;
  }
}
