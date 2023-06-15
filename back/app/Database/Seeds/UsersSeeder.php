<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User;

class UsersSeeder extends Seeder
{
    public function run()
    {
        $users = auth()->getProvider();

        $user = new User([
            'username' => 'dev',
            'email'    => 'dev@example.com',
            'password' => 'dev',
            'active' => true,
        ]);
        $users->save($user);
    }
}
