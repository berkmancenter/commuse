<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNewsTable extends Migration
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
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => '1000',
                'default'    => '',
            ],
            'author' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'default'    => '',
            ],
            'image_url' => [
                'type'       => 'VARCHAR',
                'constraint' => '1000',
                'default'    => '',
            ],
            'short_description' => [
                'type' => 'TEXT',
                'default'    => '',
            ],
            'description' => [
                'type' => 'TEXT',
                'default'    => '',
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
        $this->forge->createTable('news');
    }

    public function down()
    {
        $this->forge->dropTable('news');
    }
}
