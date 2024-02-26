<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\PeopleModel;
use App\Libraries\UserProfileStructure;

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
}
