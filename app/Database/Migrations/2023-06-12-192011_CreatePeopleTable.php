<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePeopleTable extends Migration
{
    public function up()
    {
        $fields = [
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'first_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'default'    => '',
            ],
            'last_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'default'    => '',
            ],
            'image_url' => [
                'type'       => 'VARCHAR',
                'constraint' => '1000',
                'default'    => '',
            ],
            'twitter_url' => [
                'type'       => 'VARCHAR',
                'constraint' => '1000',
                'default'    => '',
            ],
            'linkedin_url' => [
                'type'       => 'VARCHAR',
                'constraint' => '1000',
                'default'    => '',
            ],
            'mastodon_url' => [
                'type'       => 'VARCHAR',
                'constraint' => '1000',
                'default'    => '',
            ],
            'short_bio' => [
                'type' => 'TEXT',
                'default'    => '',
            ],
            'bio' => [
                'type' => 'TEXT',
                'default'    => '',
            ],
            'continent' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'default'    => '',
            ],
            'country' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'default'    => '',
            ],
            'city' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'default'    => '',
            ],
            'topics' => [
                'type' => 'JSONB',
                'default'    => '[]',
            ],
            'user_id' => [
                'type' => 'INT',
                'null'       => false,
                'unique'     => true,
            ],
            'public_profile' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
            ],
            'created_at' => [
                'type' => 'datetime',
                'null'       => false,
                'default' => 'NOW()',
            ],
            'updated_at' => [
                'type' => 'datetime',
                'null'       => false,
                'default' => 'NOW()',
            ],
        ];

        $this->forge->addField($fields);
        $this->forge->addKey('id', true);
        $this->forge->createTable('people');
    }

    public function down()
    {
        $this->forge->dropTable('people');
    }
}
