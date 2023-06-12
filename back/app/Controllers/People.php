<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

class People extends BaseController
{
  use ResponseTrait;

  public function index()
  {
    $peopleModel = model('PeopleModel');

    $people = $peopleModel
      ->orderBy('id', 'desc')
      ->findAll();

    return $this->respond($people);
  }
}
