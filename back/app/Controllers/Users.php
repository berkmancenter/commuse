<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\PeopleModel;
use App\Models\UserModel;
use League\Csv\Reader;
use League\Csv\Statement;
use CodeIgniter\Shield\Entities\User;

class Users extends BaseController
{
  use ResponseTrait;

  public function current()
  {
    $user = auth()->user();
    $userId = auth()->id();

    $userData['id'] = $userId;
    $userData['admin'] = $user->inGroup('admin');

    return $this->respond($userData);
  }

  public function currentProfile()
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

    $jsonKeysToMap = [
      'affiliation', 'interested_in', 'knowledgeable_in',
      'working_groups', 'projects'
    ];

    foreach ($jsonKeysToMap as $key) {
      $personData[$key] = json_decode($personData[$key] ?? '[]');
    }

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
      'employer_name', 'job_title', 'industry',
    ];

    $data = $this->mapRequestData($requestData, $keysToMap);
    $data['public_profile'] = $requestData['public_profile'] ?? false;
    $data['user_id'] = $userId;

    $jsonKeysToMap = [
      'affiliation', 'interested_in', 'knowledgeable_in',
      'working_groups', 'projects',
    ];
    foreach ($jsonKeysToMap as $key) {
      $data[$key] = json_encode($requestData[$key] ?? []);
    }

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
      'user_id'  => $userId,
    ];

    $result = $existingPerson ? $peopleModel->update($existingPerson['id'], $data) : $peopleModel->insert($data);

    $message = $existingPerson ? 'Profile image updated successfully' : 'Profile image created successfully';

    if ($result) {
      return $this->respond([
        'message' => $message,
        'image' => "profile_images/{$fileName}",
      ], 200);
    } else {
      return $this->respond(['message' => 'Error saving data'], 500);
    }
  }

  public function adminIndex()
  {
    $this->checkAdminAccess();

    $db = \Config\Database::connect();
    $query = $db->table('users')
      ->select('
        users.id,
        users.username,
        users.created_at,
        users.last_active AS last_login,
        people.first_name,
        people.last_name,
        auth_identities.secret AS email,
        STRING_AGG(auth_groups_users.group, \',\') AS groups
      ')
      ->join('people', 'people.user_id = users.id', 'left')
      ->join('auth_identities', 'auth_identities.user_id = users.id', 'left')
      ->join('auth_groups_users', 'auth_groups_users.user_id = users.id', 'left')
      ->groupBy('users.id, people.first_name, people.last_name, auth_identities.secret, auth_identities.last_used_at')
      ->get();

    $users = $query->getResultArray();

    foreach ($users as &$user) {
      $user['groups'] = array_map('trim', array_filter(explode(',', $user['groups'])));
    }

    return $this->respond($users);
  }

  public function delete()
  {
    $this->checkAdminAccess();

    $result = false;
    $usersModel = model('UserModel');
    $peopleModel = model('PeopleModel');
    $requestData = json_decode(file_get_contents('php://input'), true);
    $userIds = $requestData['users'] ?? [];

    if (!empty($userIds)) {
      $result = $usersModel->whereIn('id', $userIds)->delete();

      if ($result) {
        $result = $peopleModel->whereIn('user_id', $userIds)->delete();
      }
    }

    if ($result) {
      return $this->respond(['message' => 'Users have been removed successfully.'], 200);
    } else {
      return $this->respond(['message' => 'Error removing users.'], 500);
    }
  }

  public function changeRole()
  {
    $this->checkAdminAccess();

    $result = false;
    $requestData = json_decode(file_get_contents('php://input'), true);
    $userIds = $requestData['users'] ?? [];
    $role = $requestData['role'] ?? [];
    $db = \Config\Database::connect();

    if (!empty($userIds) && !empty($role)) {
      $db->transStart();

      $db->table('auth_groups_users')
        ->where('user_id', $userIds)
        ->delete();

      $newGroupsUsers = [];
      foreach($userIds as $userId) {
        $newGroupsUsers[] = [
          'user_id' => $userId,
          'group' => $role,
          'created_at' => new \CodeIgniter\Database\RawSql('NOW()'),
        ];
      }
      $db->table('auth_groups_users')
        ->insertBatch($newGroupsUsers);

      $db->transComplete();

      $result = $db->transStatus();
    }

    if ($result) {
      return $this->respond(['message' => 'User role has been set successfully.'], 200);
    } else {
      return $this->respond(['message' => 'Error changing user role.'], 500);
    }
  }

  public function importFromCsv()
  {
    $this->checkAdminAccess();

    helper('text');

    $result = false;
    $count = 0;
    $peopleModel = new PeopleModel();
    $userModel = new UserModel();
    $usersProvider = auth()->getProvider();
    $db = \Config\Database::connect();

    $file = $this->request->getFile('csv');

    if (!$file->isValid()) {
      return;
    }

    $fileName = $file->getRandomName();
    $dirPath = ROOTPATH . 'writable/uploads/import_users_csvs/';
    if (!is_dir($dirPath)) {
      mkdir($dirPath);
    }
    $file->move($dirPath, $fileName);

    try {
      $csv = Reader::createFromPath("{$dirPath}/{$fileName}", 'r');
      $csv->setHeaderOffset(0);
  
      $stmt = Statement::create();
      $records = $stmt->process($csv);
      foreach ($records as $record) {
        if (!$record['email']) {
          continue;
        }

        try {
          $keysToMap = [
            'prefix', 'first_name', 'middle_name', 'last_name', 'preferred_name',
            'preferred_pronouns', 'bio', 'website_link', 'facebook_link', 'twitter_link',
            'linkedin_link', 'mastodon_link', 'instagram_link', 'snapchat_link', 'other_link',
            'mobile_phone_number', 'email', 'home_city', 'home_state_province', 'home_country',
            'employer_name', 'job_title', 'industry',
          ];
          $peopleData = $this->mapRequestData($record, $keysToMap);

          $newUserData  = [
            'username' => substr(md5(mt_rand()), 0, 10) . substr(md5($record['email']), 0, 20),
            'email'    => $record['email'],
            'password' => bin2hex(random_bytes(10)),
          ];

          $user = new User($newUserData);
          $saved = $usersProvider->save($user);

          if ($saved) {
            $userId = $usersProvider->getInsertID();
            $peopleData['user_id'] = $userId;
            $peopleModel->insert($peopleData);
            $count++;
          }
        } catch (\Throwable $exceptionRecord) {
          error_log($exceptionRecord->getMessage());
        }
      }

      $result = true;
    } catch (\Throwable $exception) {
      error_log($exception->getMessage());
    }

    if ($result) {
      return $this->respond([
        'message' => "Imported {$count} new users.",
      ], 200);
    } else {
      return $this->respond(['message' => 'Error importing users.'], 500);
    }
  }
}
