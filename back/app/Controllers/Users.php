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

    if (isset($personData['image_url']) && $personData['image_url']) {
        $personData['image_url'] = "profile_images/{$personData['image_url']}";
    }

    $personData['topics'] = json_decode($personData['topics']);
    $personData['affiliation'] = json_decode($personData['affiliation']);
    $personData['affiliation_years'] = json_decode($personData['affiliation_years']);
    $personData['interested_in'] = json_decode($personData['interested_in']);
    $personData['knowledgeable_in'] = json_decode($personData['knowledgeable_in']);
    $personData['working_groups'] = json_decode($personData['working_groups']);
    $personData['projects'] = json_decode($personData['projects']);

    return $this->respond($personData);
  }

  public function saveProfile()
  {
      $peopleModel = new PeopleModel();
      $userId = auth()->id();

      $existingPerson = $peopleModel->where('user_id', $userId)->first();

      $requestData = json_decode(file_get_contents('php://input'), true);

      $keysToMap = [
          'prefix', 'first_name', 'middle_name', 'last_name', 'preferred_name',
          'preferred_pronouns', 'bio', 'website_link', 'facebook_link', 'twitter_link',
          'linkedin_link', 'mastodon_link', 'instagram_link', 'snapchat_link', 'other_link',
          'mobile_phone_number', 'email', 'home_city', 'home_state_province', 'home_country',
          'employer_name', 'job_title', 'industry', 'affiliation', 'affiliation_years',
          'interested_in', 'knowledgeable_in', 'working_groups', 'projects'
      ];

      $data = $this->mapRequestData($requestData, $keysToMap);
      $data['affiliation'] = json_encode($data['affiliation'] ?? []);
      $data['affiliation_years'] = json_encode($data['affiliation_years'] ?? []);
      $data['interested_in'] = json_encode($data['interested_in'] ?? []);
      $data['knowledgeable_in'] = json_encode($data['knowledgeable_in'] ?? []);
      $data['working_groups'] = json_encode($data['working_groups'] ?? []);
      $data['projects'] = json_encode($data['projects'] ?? []);
      $data['user_id'] = $userId;

      $message = $existingPerson ? 'Profile updated successfully' : 'Profile created successfully';

      $result = $existingPerson ? $peopleModel->update($existingPerson['id'], $data) : $peopleModel->insert($data);

      if ($result) {
          return $this->respond(['message' => $message], 200);
      } else {
          return $this->respond(['message' => 'Error saving data'], 500);
      }
  }

  public function uploadProfileImage()
  {
      helper('text');

      $peopleModel = new PeopleModel();
      $userId = auth()->id();

      $existingPerson = $peopleModel->where('user_id', $userId)->first();

      $file = $this->request->getFile('image');

      if (!$file->isValid()) {
        return;
      }

      $fileName = $file->getRandomName();
      $dirPath = ROOTPATH . 'writable/uploads/profile_images/';
      if (!is_dir($dirPath)) {
          mkdir($dirPath);
      }
      $file->move($dirPath, $fileName);

      $data = [
          'image_url'  => $fileName,
          'user_id'    => $userId,
      ];

      $result = $existingPerson ? $peopleModel->update($existingPerson['id'], $data) : $peopleModel->insert($data);

      $message = $existingPerson ? 'Profile image updated successfully' : 'Profile image created successfully';

      if ($result) {
          return $this->respond([
              'message' => 'ok',
              'image' => "profile_images/{$fileName}",
          ], 200);
      } else {
          return $this->respond(['message' => 'Error saving data'], 500);
      }
  }

  private function mapRequestData($requestData, $keys)
  {
      $mappedData = [];

      foreach ($keys as $key) {
          $mappedData[$key] = $requestData[$key] ?? '';
      }

      return $mappedData;
  }
}
