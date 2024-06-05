<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRemoteFieldsToNews extends Migration
{
    public function up()
    {
        $fields = [
            'remote_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'default'    => '',
            ],
            'remote_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 1000,
                'default'    => '',
            ],
        ];

        $this->forge->addColumn('news', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('news', ['remote_id', 'remote_url']);
    }
}
