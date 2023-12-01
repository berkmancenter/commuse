<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCustomFields extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'input_type' => [
                'type' => 'VARCHAR',
               'constraint' => 50,
                'null' => false,
            ],
            'machine_name' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => false,
            ],
            'model_name' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
            ],
            'group_id' => [
              'type' => 'INT',
              'unsigned' => true,
              'null' => true,
            ],
            'order' => [
                'type' => 'INT',
                'unsigned' => true,
                'default' => 1,
            ],
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => false,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'metadata' => [
                'type' => 'JSONB',
                'default' => '{}',
                'null' => false,
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
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('custom_fields');
    }

    public function down()
    {
        $this->forge->dropTable('custom_fields');
    }
}
