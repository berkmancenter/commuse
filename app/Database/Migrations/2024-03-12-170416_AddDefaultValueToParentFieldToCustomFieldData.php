<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDefaultValueToParentFieldToCustomFieldData extends Migration
{
    public function up()
    {
      $this->db->simpleQuery('ALTER TABLE ONLY custom_field_data ALTER COLUMN parent_field_value_index SET DEFAULT \'0\';');
    }

    public function down()
    {
      $this->db->simpleQuery('ALTER TABLE ONLY custom_field_data ALTER COLUMN parent_field_value_index DROP DEFAULT ;');
    }
}
