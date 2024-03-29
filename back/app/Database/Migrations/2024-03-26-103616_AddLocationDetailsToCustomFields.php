<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLocationDetailsToCustomFields extends Migration
{
  public function up() {
    $fieldsData = <<<EOD
    INSERT INTO custom_fields (input_type, machine_name, model_name, group_id, "order", title, description, metadata, created_at, updated_at) VALUES ('short_text', 'current_location_lat', 'People', 0, 2, 'Current location lat', NULL, '{}', '2023-12-01 06:19:30.777015', '2023-12-01 06:19:30.777015');
    INSERT INTO custom_fields (input_type, machine_name, model_name, group_id, "order", title, description, metadata, created_at, updated_at) VALUES ('short_text', 'current_location_lon', 'People', 0, 2, 'Current location lon', NULL, '{}', '2023-12-01 06:19:30.777015', '2023-12-01 06:19:30.777015');
    EOD;

    $this->db->query($fieldsData);
  }

  public function down() {}
}
