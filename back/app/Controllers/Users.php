<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\PeopleModel;
use App\Models\UserModel;
use League\Csv\Reader;
use League\Csv\Statement;
use CodeIgniter\Shield\Entities\User;
use App\Validation\ChangePasswordValidationRules;

class Users extends BaseController
{
  use ResponseTrait;

  public function current()
  {
    $user = auth()->user();

    $userData = [
      'id' => $user->id,
      'email' => $user->email,
      'admin' => $user->inGroup('admin'),
    ];

    return $this->respond($userData);
  }

  public function currentProfile()
  {
    $usersModel = new UserModel();
    $userId = auth()->id();
    $userData = $usersModel->getUserProfileData($userId);

    if (!$userData) {
      return $this->respond([]);
    }

    $userData['public_profile'] = $userData['public_profile'] == 't';

    if (isset($userData['image_url']) && $userData['image_url']) {
      $userData['image_url'] = "profile_images/{$userData['image_url']}";
    }

    return $this->respond($userData);
  }

  public function profileStructure()
  {
    $usersModel = new UserModel();
    $profileStructure = $usersModel->getUserProfileStructure();

    return $this->respond($profileStructure);
  }

  public function saveProfile()
  {
    $userModel = new UserModel();
    $requestData = json_decode(file_get_contents('php://input'), true);

    list($result, $message) = $userModel->saveProfileData($requestData);

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
    $usersProvider = auth()->getProvider();
    $peopleModel = model('PeopleModel');
    $requestData = json_decode(file_get_contents('php://input'), true);
    $userIds = $requestData['users'] ?? [];

    if (!empty($userIds)) {
      try {
        foreach ($userIds as $userId) {
          $usersProvider->delete($userId, true);
        }
        $peopleModel->whereIn('user_id', $userIds)->delete();
        $result = true;
      } catch (\Throwable $exception) {
        error_log($exception->getMessage());
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
    $userModel = new UserModel();
    $usersProvider = auth()->getProvider();

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
          $saved = false;

          $newUserData  = [
            'username' => substr(md5(mt_rand()), 0, 10) . substr(md5($record['email']), 0, 20),
            'email'    => $record['email'],
            'password' => bin2hex(random_bytes(10)),
            'active'   => true,
          ];

          $user = new User($newUserData);
          $saved = $usersProvider->save($user);

          if ($saved) {
            $userId = $usersProvider->getInsertID();
            $userSaved = $usersProvider->findById($userId);
            $userSaved->forcePasswordReset();
            $userModel->saveProfileData($record, $userId);
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

  public function changePasswordView()
  {
    if (!auth()->loggedIn()) {
      return redirect()->to(config('Auth')->login());
    }

    return view('\App\Views\Shield\changePassword');
  }

  public function changePassword()
  {
    if (!auth()->loggedIn()) {
      return redirect()->to(config('Auth')->login());
    }

    $request = service('request');

    $rulesInstance = new ChangePasswordValidationRules();
    $rules = $rulesInstance->getRules();

    if (!$this->validateData($this->request->getPost(), $rules)) {
      if ($request->getHeaderLine('Accept') === 'application/json') {
        return $this->respond(['message' => array_values($this->validator->getErrors())], 422);
      } else {
        return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
      }
    }

    $usersProvider = auth()->getProvider();
    $user = auth()->user();
    $user->password = $this->request->getPost()['password'];
    $usersProvider->save($user);
    $user->undoForcePasswordReset();

    if ($request->getHeaderLine('Accept') === 'application/json') {
      return $this->respond(['message' => 'Password has been updated successfully.']);
    } else {
      return redirect()->to(config('Auth')->registerRedirect());
    }
  }
}
