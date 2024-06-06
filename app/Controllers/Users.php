<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\PeopleModel;
use App\Models\UserModel;
use League\Csv\Reader;
use League\Csv\Statement;
use CodeIgniter\Shield\Entities\User;
use App\Validation\ChangePasswordValidationRules;
use \Gumlet\ImageResize;

/**
 * This class is responsible for handling user-related operations such as
 * retrieving user data, managing profiles, and performing administrative tasks.
 */
class Users extends BaseController
{
  use ResponseTrait;

  /**
   * Get the current user's information
   *
   * @return \CodeIgniter\HTTP\Response
   */
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

  /**
   * Get user profile data
   *
   * @param int|null $id User ID (optional)
   * @return \CodeIgniter\HTTP\Response
   */
  public function profile($id = null)
  {
    $usersModel = new UserModel();

    $userId = auth()->id();

    if (auth()->user()->can('admin.access') === true && $id && $id !== 'current') {
      $userId = $id;
    }

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

  /**
   * Get user profile structure
   *
   * @return \CodeIgniter\HTTP\Response
   */
  public function profileStructure()
  {
    $usersModel = new UserModel();
    $profileStructure = $usersModel->getUserProfileStructure();

    return $this->respond($profileStructure);
  }

  /**
   * Save user profile data
   *
   * @return \CodeIgniter\HTTP\Response
   */
  public function saveProfile()
  {
    $userModel = new UserModel();
    $requestData = $this->request->getJSON(true);

    list($result, $message) = $userModel->saveProfileData($requestData);

    var_dump($result);
    var_dump($message);

    if ($result) {
      return $this->respond(['message' => $message], 200);
    } else {
      return $this->respond(['message' => 'Error saving data'], 500);
    }
  }

  /**
   * Upload user profile image
   *
   * @param int|null $id User ID (optional)
   * @return \CodeIgniter\HTTP\Response
   */
  public function uploadProfileImage($id = null)
  {
    helper('text');
    $peopleModel = new PeopleModel();
    $userId = auth()->id();

    if (auth()->user()->can('admin.access') === true && $id && $id !== 'current') {
      $userId = $id;
    }

    $existingPerson = $peopleModel->where('user_id', $userId)->first();

    $file = $this->request->getFile('image');

    if (!$file->isValid()) {
      return $this->respond(['message' => 'Error saving data.'], 400);
    }

    $fileName = $file->getRandomName();
    $dirPath = ROOTPATH . 'writable/uploads/profile_images/';
    if (!is_dir($dirPath)) {
      mkdir($dirPath);
    }
    $file->move($dirPath, $fileName);

    $image = new ImageResize("{$dirPath}/{$fileName}");
    $image->resizeToWidth(300);
    $image->save("{$dirPath}/{$fileName}");

    $data = [
      'image_url'  => $fileName,
      'user_id'  => $userId,
    ];

    $result = $existingPerson ? $peopleModel->update($existingPerson['id'], $data) : $peopleModel->insert($data);

    $message = $existingPerson ? 'Profile image updated successfully' : 'Profile image created successfully';

    // Add audit record
    $db = \Config\Database::connect();
    $builder = $db->table('audit');
    $builder->insert([
      'audited_id' => $userId,
      'model_name' => 'People',
      'changed_user_id' => auth()->id(),
      'changes' => json_encode([
        'new' => [
          'profile_image' => 'new profile image',
        ],
        'old' => [
          'profile_image' => '',
        ],
      ]),
    ]);

    if ($result) {
      return $this->respond([
        'message' => $message,
        'image' => "profile_images/{$fileName}",
      ], 200);
    } else {
      return $this->respond(['message' => 'Error saving data'], 500);
    }
  }

  /**
   * Get the list of users for the admin panel
   *
   * @return \CodeIgniter\HTTP\Response
   */
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
        people.id AS people_id,
        people.reintake,
        auth_identities.secret AS email,
        invitation_codes.code as invitation_code,
        STRING_AGG(auth_groups_users.group, \',\') AS groups
      ')
      ->join('people', 'people.user_id = users.id', 'left')
      ->join('auth_identities', 'auth_identities.user_id = users.id', 'left')
      ->join('auth_groups_users', 'auth_groups_users.user_id = users.id', 'left')
      ->join('invitation_codes', 'invitation_codes.id = users.invitation_code_id', 'left')
      ->where('auth_identities.type', 'email_password')
      ->groupBy('users.id, people.first_name, people.last_name, auth_identities.secret, auth_identities.last_used_at, people.id, invitation_codes.code')
      ->get();

    $users = $query->getResultArray();

    foreach ($users as &$user) {
      $user['groups'] = array_map('trim', array_filter(explode(',', $user['groups'])));
    }

    return $this->respond($users);
  }

  /**
   * Delete user(s)
   *
   * @return \CodeIgniter\HTTP\Response
   */
  public function delete()
  {
    $this->checkAdminAccess();

    $result = false;
    $usersProvider = auth()->getProvider();
    $peopleModel = model('PeopleModel');
    $db = \Config\Database::connect();
    $requestData = $this->request->getJSON(true);
    $userIds = $requestData['users'] ?? [];
    $userIds = array_map('intval', $userIds);
    $userIds = array_diff($userIds, [auth()->id()]);

    if (count($userIds) === 0) {
      $okMessage = 'Users have been removed successfully.';
    } else {
      $okMessage = 'User has been removed successfully.';
    }

    if (!empty($userIds)) {
      try {
        foreach ($userIds as $userId) {
          $usersProvider->delete($userId, true);
        }

        $peopleModel->whereIn('user_id', $userIds)->delete();
        $customFieldsData = $db->table('custom_field_data')
          ->select('custom_field_data.id')
          ->join('custom_fields', 'custom_fields.id = custom_field_data.custom_field_id')
          ->where('custom_fields.model_name', 'People')
          ->whereIn('model_id', $userIds)
          ->get()
          ->getResultArray();

        $customFieldsDataIds = array_map(function ($customFieldsDataItem) {
          return $customFieldsDataItem['id'];
        }, $customFieldsData);

        if (empty($customFieldsDataIds) === false) {
          $db
            ->table('custom_field_data')
            ->whereIn('id', $customFieldsDataIds)
            ->delete();
        }

        $result = true;
      } catch (\Throwable $exception) {
        error_log($exception->getMessage());
      }
    }

    if ($result) {
      return $this->respond(['message' => $okMessage], 200);
    } else {
      return $this->respond(['message' => 'Error removing users.'], 500);
    }
  }

  /**
   * Change user role
   *
   * @return \CodeIgniter\HTTP\Response
   */
  public function changeRole()
  {
    $this->checkAdminAccess();

    $result = false;
    $requestData = $this->request->getJSON(true);
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

  /**
   * Import users from CSV
   *
   * @return \CodeIgniter\HTTP\Response
   */
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
      return $this->respond(['message' => 'Error saving data.'], 400);
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

            $customFieldsModel = model('CustomFieldModel');
            $customFields = $customFieldsModel
              ->select('machine_name')
              ->whereIn('input_type', ['tags'])
              ->findAll();

            $customFieldsMachineNames = array_map(function ($customField) {
              return $customField['machine_name'];
            }, $customFields);

            array_walk($record, function (&$recordField, $key) use ($customFieldsMachineNames) {
              if (in_array($key, $customFieldsMachineNames)) {
                $recordField = explode(',', $recordField);
              }
            });

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
      if ($count > 0) {
        $cache = \Config\Services::cache();
        $cache->delete('filters_with_values');
      }

      return $this->respond([
        'message' => "Imported {$count} new users.",
      ], 200);
    } else {
      return $this->respond(['message' => 'Error importing users.'], 500);
    }
  }

  /**
   * Get users CSV import template
   *
   * @return \CodeIgniter\HTTP\Response
   */
  public function getUsersCsvImportTemplate() {
    $csvImportFields = [];
    $csvImportFields = array_merge($csvImportFields, array_diff(UserModel::BASE_FIELDS, ['public_profile']));

    $customFieldsModel = model('CustomFieldModel');
    $customFields = $customFieldsModel
      ->select('machine_name')
      ->whereIn('input_type', ['short_text', 'long_text', 'tags'])
      ->findAll();

    $customFieldsMachineNames = array_map(function ($customField) {
      return $customField['machine_name'];
    }, $customFields);
    $csvImportFields = array_merge($csvImportFields, $customFieldsMachineNames);

    $textData = join(',', $csvImportFields);

    $response = service('response');
    $response->setContentType('text/csv');

    return $response->download('users_import_template.csv', $textData);
  }

  /**
   * Change user password view
   *
   * @return mixed
   */
  public function changePasswordView()
  {
    if (!auth()->loggedIn()) {
      return redirect()->to(config('Auth')->login());
    }

    return view('\App\Views\Shield\change_password');
  }

  /**
   * Change user password
   *
   * @return \CodeIgniter\HTTP\Response
   */
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

  /**
   * Set Reintake status
   *
   * @return \CodeIgniter\HTTP\Response
   */
  public function setReintakeStatus()
  {
    $this->checkAdminAccess();

    try {
      $requestData = $this->request->getJSON(true);
      $userIds = $requestData['users'] ?? [];
      $status = $requestData['status'] ?? 'not_required';
      $usersModel = new UserModel();
  
      if (!empty($userIds)) {
        foreach ($userIds as $userId) {
          $userData = $usersModel->getUserProfileData($userId);
          $userData['reintake'] = $status;
          $usersModel->saveProfileData($userData, $userId);
        }
      }
    } catch (\Throwable $th) {
      return $this->respond(['message' => 'Error changing Reintake status.'], 500);
    }

    return $this->respond(['message' => 'Reintake status has been set successfully.'], 200);
  }

  /**
   * Reintake view
   *
   * @return string
   */
  public function reintakeView()
  {
    return view('\App\Views\Users\reintake');
  }

  /**
   * Handle the acceptance of a reintake request.
   *
   * This method sets the reintake status of the user to "accepted" and redirects
   * the user to their profile page.
   *
   * @return \CodeIgniter\HTTP\RedirectResponse The redirect response to the user's profile page.
   */
  public function reintakeAccept()
  {
    $this->reintakeSet(UserModel::REINTAKE_STATUS_ACCEPTED);

    return redirect()->to(site_url('profile'));
  }

  /**
   * Handle the denial of a reintake request.
   *
   * This method sets the reintake status of the user to "denied" and logs the user out.
   *
   * @return \CodeIgniter\HTTP\RedirectResponse The redirect response to the home page.
   */
  public function reintakeDeny()
  {
    $this->reintakeSet(UserModel::REINTAKE_STATUS_DENIED);
    auth()->logout();

    return redirect()->to(site_url());
  }

  /**
   * Set reintake status and send notification email
   *
   * @param string $status
   * @return void
   */
  private function reintakeSet($status) {
    $userId = auth()->id();
    $usersModel = new UserModel();

    $db = \Config\Database::connect();
    $builder = $db->table('people p');
    $person = $builder
      ->select('*')
      ->where('user_id', $userId)
      ->get()
      ->getResultArray();

    if (count($person)) {
      if ($person[0]['reintake'] === UserModel::REINTAKE_STATUS_REQUIRED) {
        $userData = $usersModel->getUserProfileData($userId);
        $userData['reintake'] = $status;
        $usersModel->saveProfileData($userData, $userId);

        $this->reintakeSendNotificationEmail($status, $person[0]);
      }
    }
  }

  /**
   * Sends a notification email for the reintake status.
   *
   * @param string $status The reintake status.
   * @param array $person The person's information.
   * @return void
   */
  private function reintakeSendNotificationEmail($status, $person) {
    $email = \Config\Services::email();
    $name = $person['first_name'] . ' ' . $person['last_name'];
    $userEmail = $person['email'];

    $email->setTo($_ENV['reintake.notificationEmails']);
    $email->setSubject("Reintake {$status} by {$name} {$userEmail}");
    $email->setMessage("Reintake has been {$status} by {$name} {$userEmail}.");

    $email->send();
  }
}
