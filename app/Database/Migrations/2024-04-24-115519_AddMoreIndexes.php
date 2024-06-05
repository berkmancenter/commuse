<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMoreIndexes extends Migration
{
    public function up() {
      $indexes = <<<EOD
        CREATE INDEX custom_fields_machine_name ON custom_fields(machine_name);
        CREATE INDEX custom_fields_model_name ON custom_fields(model_name);
        CREATE INDEX custom_fields_group_id ON custom_fields(group_id);
        CREATE INDEX custom_field_data_model_id ON custom_field_data(model_id);
        CREATE INDEX custom_field_data_custom_field_id ON custom_field_data(custom_field_id);
      EOD;

      $this->db->query($indexes);
    }

    public function down()
    {
      $indexes = <<<EOD
        DROP INDEX custom_fields_machine_name;
        DROP INDEX custom_fields_model_name;
        DROP INDEX custom_fields_group_id;
        DROP INDEX custom_field_data_model_id;
        DROP INDEX custom_field_data_custom_field_id;
      EOD;

      $this->db->query($indexes);
    }
}
