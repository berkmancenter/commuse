<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIndexesToCustomFieldDataAndPeople extends Migration
{
    public function up() {
      $indexes = <<<EOD
        CREATE INDEX people_full_text_search ON people USING GIN(to_tsvector('english', LOWER(full_text_search)));
        CREATE INDEX custom_field_data_value ON custom_field_data(value);
      EOD;

      $this->db->query($indexes);
    }

    public function down()
    {
      $indexes = <<<EOD
        DROP INDEX IF EXISTS people_full_text_search;
        DROP INDEX IF EXISTS custom_field_data_value;
      EOD;

      $this->db->query($indexes);
    }
}
