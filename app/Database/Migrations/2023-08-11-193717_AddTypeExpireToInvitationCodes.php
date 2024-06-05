<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTypeExpireToInvitationCodes extends Migration
{
    public function up()
    {
        $fields = [
            'type' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => false,
                'default'    => 'single',
            ],
            'expire' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ];

        $this->forge->addColumn('invitation_codes', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('invitation_codes', ['type', 'expire']);
    }
}
