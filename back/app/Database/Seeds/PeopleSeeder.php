<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User;

class PeopleSeeder extends Seeder
{
    public function run()
    {
        $faker = \Faker\Factory::create();
        $users = auth()->getProvider();

        $topics = [
            'AI',
            'ethics',
            'justice',
            'privacy',
            'security',
            'technology',
            'justice',
            'inclusion',
            'equality',
            'law',
            'public discourse',
            'education',
        ];

        for ($i = 1; $i < 51; $i++) {
            $randomCount = rand(1, 4);
            $randomTopics = array_values(array_intersect_key($topics, array_flip((array) array_rand($topics, $randomCount))));

            $username = $faker->userName;
            $email = $faker->unique()->safeEmail;

            $user = new User([
                'username' => $username,
                'email'    => $email,
                'password' => 'dev',
                'active'   => true,
            ]);
            $users->save($user);

            $data = [
                'first_name'        => $faker->firstName,
                'last_name'         => $faker->lastName,
                'image_url'         => $_ENV['app.baseURL'] . '/seeds/people/' . $i . '.jpg',
                'short_bio'         => $faker->paragraphs(2, true),
                'public_profile'    => true,
                'topics'            => json_encode($randomTopics),
                'user_id'           => $users->getInsertID(),
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ];

            $this->db->table('people')->insert($data);
        }
    }
}
