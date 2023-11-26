<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCustomFieldGroups extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'machine_name' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => false,
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
            'order' => [
                'type' => 'INT',
                'unsigned' => true,
                'default' => 1,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('custom_field_groups');
    }

    public function down()
    {
        $this->forge->dropTable('custom_field_groups');
    }
}
