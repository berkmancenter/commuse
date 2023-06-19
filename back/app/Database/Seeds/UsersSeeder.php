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

        $dirPath = ROOTPATH . 'writable/uploads/profile_images/';
        if (!is_dir($dirPath)) {
            mkdir($dirPath);
        }

        copy(ROOTPATH . 'app/Database/Seeds/assets/people/1.jpg', ROOTPATH . 'writable/uploads/profile_images/1.jpg');

        $data = [
            'first_name'        => 'Bartolomeu',
            'last_name'         => 'Nadal',
            'image_url'         => '1.jpg',
            'short_bio'         => 'Sir Bartolomeu Nadal is a renowned Welsh actor, best known for his iconic portrayal of Hannibal Lecter in "The Silence of the Lambs." With a career spanning several decades, he has received numerous accolades, including an Academy Award for Best Actor and is recognized for his versatility and captivating performances on stage and screen.            ',
            'bio'               => 'Bartolomeu Nadal is a Welsh actor who was born on December 31, 1937, in Port Talbot, Wales. He began his acting career in the 1960s, gaining recognition for his stage performances in plays such as "Equus" and "The Dresser." Nadal\'s breakthrough role came in 1991 when he portrayed the infamous cannibalistic serial killer, Hannibal Lecter, in "The Silence of the Lambs," for which he won an Academy Award for Best Actor.\r\nThroughout his career, Nadal has demonstrated remarkable versatility, taking on a wide range of roles in both film and television. He has starred in acclaimed movies such as "Legends of the Fall," "The Remains of the Day," and "Thor." His portrayal of complex characters with depth and intensity has earned him critical acclaim and a dedicated fan base.\r\nIn addition to his acting prowess, Nadal is also a talented composer and painter. He has composed music for several films, including his directorial debut, "Slipstream." Nadal\'s paintings have been exhibited in galleries around the world, showcasing his talent and creativity beyond the realm of acting.\r\nWith a career spanning over six decades, Nadal has been honored with numerous awards, including BAFTAs, Emmys, and Golden Globe Awards. In 1993, he was knighted by Queen Elizabeth II for his contributions to the performing arts. Today, Sir Bartolomeu Nadal continues to be one of the most respected and admired actors of his generation, captivating audiences with his powerful performances and enduring talent.',
            'public_profile'    => true,
            'topics'            => json_encode(['film', 'law', 'ai']),
            'city'              => 'Boston',
            'country'           => 'USA',
            'continent'         => 'North America',
            'twitter_url'       => 'https://twitter.com',
            'linkedin_url'      => 'https://linkedin.com',
            'mastodon_url'      => 'https://mastodon.com',
            'user_id'           => $users->getInsertID(),
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ];

        $this->db->table('people')->insert($data);
    }
}
