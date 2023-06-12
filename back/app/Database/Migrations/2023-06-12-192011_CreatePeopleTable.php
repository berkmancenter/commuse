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
            'bio' => [
                'type' => 'TEXT',
                'default'    => '',
            ],
            'topics' => [
                'type' => 'JSONB',
                'default'    => '[]',
            ],
            'user_id' => [
              'type' => 'INT',
              'default'    => null,
            ],
            'created_at' => [
                'type' => 'datetime',
            ],
            'updated_at' => [
                'type' => 'datetime',
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
