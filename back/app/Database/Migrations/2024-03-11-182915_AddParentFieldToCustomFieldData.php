<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddParentFieldToCustomFieldData extends Migration
{
  public function up()
  {
    $fields = [
      'parent_field_value_index' => [
        'type' => 'INT',
        'unsigned' => true,
        'null' => true,
      ],
    ];

    $this->forge->addColumn('custom_field_data', $fields);
  }

  public function down()
  {
    $this->forge->dropColumn('custom_field_data', ['parent_field_value_index']);
  }
}
