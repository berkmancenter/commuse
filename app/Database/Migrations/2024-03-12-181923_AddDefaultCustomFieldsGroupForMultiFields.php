<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDefaultCustomFieldsGroupForMultiFields extends Migration
{
    public function up()
    {
      $this->db->simpleQuery("INSERT INTO custom_field_groups (id,machine_name,title) VALUES (0, 'multi_fields_group', 'Multi fields group');");
    }

    public function down()
    {
      $this->db->simpleQuery("DELETE FROM custom_field_groups WHERE id = 0");
    }
}
