<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\PeopleModel;

class Users extends BaseController
{
  use ResponseTrait;

  public function current()
  {
    $peopleModel = new PeopleModel();
    $userId = auth()->id();
    $personData = $peopleModel->where('user_id', $userId)->first() ?? [];

    if (isset($personData['public_profile'])) {
      if ($personData['public_profile'] === 't') {
        $personData['public_profile'] = true;
      } else {
        $personData['public_profile'] = false;
      }
    }

    return $this->respond($personData);
  }

  public function saveProfile()
  {
      $peopleModel = new PeopleModel();
      $userId = auth()->id();

      $existingPerson = $peopleModel->where('user_id', $userId)->first();

      $requestData = json_decode(file_get_contents('php://input'), true);

      $data = [
          'first_name'     => $requestData['first_name'] ?? '',
          'last_name'      => $requestData['last_name'] ?? '',
          'short_bio'      => $requestData['short_bio'] ?? '',
          'bio'            => $requestData['bio'] ?? '',
          'continent'      => $requestData['continent'] ?? '',
          'country'        => $requestData['country'] ?? '',
          'city'           => $requestData['city'] ?? '',
          'twitter_url'    => $requestData['twitter_url'] ?? '',
          'linkedin_url'   => $requestData['linkedin_url'] ?? '',
          'mastodon_url'   => $requestData['mastodon_url'] ?? '',
          'public_profile' => $requestData['public_profile'] ?? '0',
          'user_id'        => $userId,
      ];

      log_message('error', print_r($data, true));

      $message = $existingPerson ? 'Profile updated successfully' : 'Profile created successfully';

      $result = $existingPerson ? $peopleModel->update($existingPerson['id'], $data) : $peopleModel->insert($data);

      if ($result) {
          return $this->respond(['message' => $message], 200);
      } else {
          return $this->respond(['message' => 'Error saving data'], 500);
      }
  }
}
