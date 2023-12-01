<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCustomFieldData extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'custom_field_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => false,
            ],
            'model_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => false,
            ],
            'value' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'value_json' => [
                'type'       => 'JSONB',
                'default'    => '[]',
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
        $this->forge->addForeignKey('custom_field_id', 'custom_fields', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addUniqueKey(['custom_field_id', 'model_id']);
        $this->forge->createTable('custom_field_data');
    }

    public function down()
    {
        $this->forge->dropTable('custom_field_data');
    }
}
