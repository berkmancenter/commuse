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
        'user_id AS id',
        'prefix',
        'first_name',
        'last_name',
        'middle_name',
        'preferred_name',
        'preferred_pronouns',
        'short_bio',
        'image_url',
        'interested_in',
        'home_city',
        'home_country',
        'home_state_province',
        'twitter_link',
        'linkedin_link',
        'mastodon_link',
      ])
      ->where('public_profile', true)
      ->orderBy('last_name', 'asc')
      ->findAll();

    $people = array_map(function ($person) {
      $person['interested_in'] = json_decode($person['interested_in']);
      $person['image_url'] = $person['image_url'] ? "profile_images/{$person['image_url']}" : '';

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

  public function person($id)
  {
    $peopleModel = model('PeopleModel');

    if (auth()->user()->can('admin.access') === false) {
      $person = $peopleModel
        ->where('user_id', $id)
        ->where('public_profile', true)
        ->first();
    } else {
      $person = $peopleModel
        ->where('user_id', $id)
        ->first();
    }

    if (empty($person)) {
      return $this->respond(['message' => 'Person not found.'], 404);
    }

    $person['image_url'] = $person['image_url'] ? "profile_images/{$person['image_url']}" : '';
    $person['affiliation'] = json_decode($person['affiliation']);
    $person['interested_in'] = json_decode($person['interested_in']);
    $person['knowledgeable_in'] = json_decode($person['knowledgeable_in']);
    $person['working_groups'] = json_decode($person['working_groups']);
    $person['projects'] = json_decode($person['projects']);

    return $this->respond($person);
  }
}
