<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddReintakeToUsers extends Migration
{
  public function up()
  {
    $fields = [
      'reintake' => [
        'type' => 'VARCHAR',
        'constraint' => 20,
        'null' => false,
        'default' => 'not_required',
      ],
    ];

    $this->forge->addColumn('people', $fields);
  }

  public function down()
  {
    $this->forge->dropColumn('people', ['reintake']);
  }
}
