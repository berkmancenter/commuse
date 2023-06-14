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
      ->select(['id', 'first_name', 'last_name', 'short_bio', 'image_url', 'topics'])
      ->orderBy('id', 'desc')
      ->findAll();

    $people = array_map(function ($person) {
      $person['topics'] = json_decode($person['topics']);

      return $person;
    }, $people);

    return $this->respond($people);
  }
}
