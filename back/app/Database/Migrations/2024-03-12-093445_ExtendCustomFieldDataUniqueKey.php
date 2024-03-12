<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ExtendCustomFieldDataUniqueKey extends Migration
{
    public function up()
    {
      $this->db->simpleQuery('ALTER TABLE custom_field_data DROP CONSTRAINT custom_field_data_custom_field_id_model_id');
      $this->db->simpleQuery('ALTER TABLE custom_field_data ADD CONSTRAINT custom_field_data_custom_field_id_model_id_parent_field_value_index UNIQUE (custom_field_id, model_id, parent_field_value_index)');
    }

    public function down()
    {
      $this->db->simpleQuery('ALTER TABLE custom_field_data DROP CONSTRAINT custom_field_data_custom_field_id_model_id_parent_field_value_index');
      $this->db->simpleQuery('ALTER TABLE custom_field_data ADD CONSTRAINT custom_field_data_custom_field_id_model_id UNIQUE (custom_field_id, model_id)');
    }
}
