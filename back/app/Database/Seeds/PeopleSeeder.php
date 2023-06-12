<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PeopleSeeder extends Seeder
{
    public function run()
    {
        $faker = \Faker\Factory::create();

        for ($i = 1; $i < 51; $i++) {
            $data = [
                'first_name'        => $faker->firstName,
                'last_name'         => $faker->lastName,
                'image_url'         => $_ENV['app.baseURL'] . '/seeds/people/' . $i . '.jpg',
                'bio'               => $faker->paragraphs(2, true),
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ];

            $this->db->table('people')->insert($data);
        }
    }
}
