<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use App\Models\UserModel;
use App\Models\PeopleModel;
use CodeIgniter\Shield\Entities\User;

class CreateSystemUser extends Migration
{
    public function up()
    {
      $newUserData  = [
        'username' => 'system_user',
        'email'    => 'system_user@example.com',
        'password' => bin2hex(random_bytes(10)),
        'active'   => true,
      ];

      $userModel = new UserModel();
      $usersProvider = auth()->getProvider();
      $user = new User($newUserData);
      $usersProvider->save($user);
      $userId = $usersProvider->getInsertID();
      $userModel->saveProfileData([
        'first_name' => 'System',
        'last_name' => 'User',
        'user_id' => $userId,
      ], $userId);
    }

    public function down()
    {
      $users = auth()->getProvider();
      $user = $users->findByCredentials(['email' => 'system_user@example.com']);
      $users->delete($user->id, true);
      $peopleModel = new PeopleModel();
      $peopleModel->whereIn('user_id', [$user->id])->delete();
    }
}
