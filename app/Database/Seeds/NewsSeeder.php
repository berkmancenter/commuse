<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class NewsSeeder extends Seeder
{
    public function run()
    {
        $faker = \Faker\Factory::create();

        for ($i = 0; $i < 50; $i++) {
            $data = [
                'title'             => $faker->sentence,
                'author'            => $faker->name,
                'image_url'         => 'https://picsum.photos/700/400?x=' . md5(openssl_random_pseudo_bytes(20)),
                'short_description' => $faker->paragraph,
                'description'       => $faker->paragraphs(3, true),
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ];

            $this->db->table('news')->insert($data);
        }
    }
}
