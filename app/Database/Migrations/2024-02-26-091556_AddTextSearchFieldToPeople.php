<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTextSearchFieldToPeople extends Migration
{
    public function up()
    {
        $fields = [
            'full_text_search' => [
                'type'       => 'TEXT',
                'default'    => '',
            ],
        ];

        $this->forge->addColumn('people', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('people', ['full_text_search']);
    }
}
