<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\PeopleModel;

class People extends BaseController
{
  use ResponseTrait;

  public function index()
  {
    $peopleModel = new PeopleModel();

    $people = $peopleModel->getPeopleWithCustomFields([
      'people.public_profile' => true,
    ]);

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
}
