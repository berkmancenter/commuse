<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddParentFieldToCustomFields extends Migration
{
  public function up()
  {
    $fields = [
      'parent_field_id' => [
        'type' => 'INT',
        'unsigned' => true,
        'null' => true,
      ],
    ];

    $this->forge->addColumn('custom_fields', $fields);
  }

  public function down()
  {
    $this->forge->dropColumn('custom_fields', ['parent_field_id']);
  }
}
