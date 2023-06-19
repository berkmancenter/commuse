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
          ->select([
              'id',
              'first_name',
              'last_name',
              'short_bio',
              'image_url',
              'topics',
              'city',
              'country',
              'continent',
              'twitter_url',
              'linkedin_url',
              'mastodon_url',
          ])
          ->where('public_profile', true)
          ->orderBy('last_name', 'asc')
          ->findAll();

      $people = array_map(function ($person) {
          $person['topics'] = json_decode($person['topics']);
          $person['image_url'] = "profile_images/{$person['image_url']}";

          return $person;
      }, $people);

      return $this->respond($people);
  }
}
