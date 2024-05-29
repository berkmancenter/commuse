<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddReviewToAudit extends Migration
{
    public function up()
    {
        $fields = [
          'review' => [
            'type' => 'VARCHAR',
            'constraint' => 20,
            'null' => false,
            'default' => 'not_required',
          ],
        ];

        $this->forge->addColumn('audit', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('audit', ['review']);
    }
}
