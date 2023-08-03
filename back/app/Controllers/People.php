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
          ->select('*')
          ->where('public_profile', true)
          ->orderBy('last_name', 'asc')
          ->findAll();

      $people = array_map(function ($person) {
          $person['interested_in'] = json_decode($person['interested_in']);
          $person['image_url'] = "profile_images/{$person['image_url']}";

          return $person;
      }, $people);

      return $this->respond($people);
  }

  public function interests()
  {
      $peopleModel = model('PeopleModel');
      $uniqueInterests = $peopleModel->getAllUniqueInterests();

      return $this->respond($uniqueInterests);
  }
}
