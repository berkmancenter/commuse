<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInvitationCodeTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 5,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'code' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
            ],
            'used' => [
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
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('invitation_codes');
    }

    public function down()
    {
        $this->forge->dropTable('invitation_codes');
    }
}
