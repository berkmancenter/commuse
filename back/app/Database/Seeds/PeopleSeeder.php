<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PeopleSeeder extends Seeder
{
    public function run()
    {
        $faker = \Faker\Factory::create();

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

            $data = [
                'first_name'        => $faker->firstName,
                'last_name'         => $faker->lastName,
                'image_url'         => $_ENV['app.baseURL'] . '/seeds/people/' . $i . '.jpg',
                'short_bio'         => $faker->paragraphs(2, true),
                'topics'            => json_encode($randomTopics),
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ];

            $this->db->table('people')->insert($data);
        }
    }
}
