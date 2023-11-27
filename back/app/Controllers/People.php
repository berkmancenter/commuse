<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

class People extends BaseController
{
  use ResponseTrait;

  public function index()
  {
    $peopleModel = model('PeopleModel');

    $people = $peopleModel->getPeopleWithCustomFields([
      'people.public_profile' => true,
    ]);

    return $this->respond($people);
  }

  public function person($id)
  {
    $peopleModel = model('PeopleModel');

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
