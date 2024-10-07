<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveTextSearchFieldFromPeople extends Migration
{
  public function up()
  {
    $this->forge->dropColumn('people', ['full_text_search']);
  }

  public function down()
  {
    $fields = [
      'full_text_search' => [
        'type'    => 'TEXT',
        'default' => '',
      ],
    ];

    $this->forge->addColumn('people', $fields);
  }
}
