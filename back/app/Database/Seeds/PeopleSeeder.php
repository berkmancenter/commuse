<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User;

class PeopleSeeder extends Seeder
{
    public function run()
    {
        $faker = \Faker\Factory::create();
        $faker->addProvider(new \Faker\Provider\en_US\Address($faker));
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

        $continents = [
            'Africa',
            'Asia',
            'Europe',
            'North America',
            'Oceania',
            'South America',
        ];

        for ($i = 1; $i < 51; $i++) {
            $randomCount = rand(1, 4);
            $randomTopics = $faker->randomElements($topics, $randomCount);

            $username = $faker->userName;
            $email = $faker->unique()->safeEmail;

            $user = new User([
                'username' => $username,
                'email'    => $email,
                'password' => 'dev',
                'active'   => true,
            ]);
            $users->save($user);

            $continent = $faker->randomElement($continents);

            $data = [
                'first_name'        => $faker->firstName,
                'last_name'         => $faker->lastName,
                'image_url'         => $_ENV['app.baseURL'] . '/seeds/people/' . $i . '.jpg',
                'short_bio'         => $faker->paragraphs(2, true),
                'public_profile'    => true,
                'topics'            => json_encode($randomTopics),
                'city'              => $faker->city,
                'country'           => $faker->country,
                'continent'         => $continent,
                'user_id'           => $users->getInsertID(),
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ];

            log_message('error', print_r($data, true));

            $this->db->table('people')->insert($data);
        }
    }
}
