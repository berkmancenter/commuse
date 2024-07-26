<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateActiveAffiliationField extends Migration
{
  public function up()
  {
      $fieldsData = <<<EOD
        UPDATE
          custom_fields cf
        SET
          "order" = cf."order" + 1
        FROM
          custom_field_groups
        WHERE
          cf.group_id = custom_field_groups.id
          AND
          custom_field_groups.machine_name = 'affiliation';

        INSERT INTO custom_fields (
            input_type, machine_name, model_name, group_id, "order", title, description, metadata, created_at, updated_at, parent_field_id
        ) VALUES (
            'tags_range', 'activeAffiliation', 'People', (SELECT id FROM custom_field_groups WHERE custom_field_groups.machine_name = 'affiliation'), 1, 'Active affiliation', '', '{ "allowMultiple": false, "possibleValues": ["Role 1", "Role 2"] }', NOW(), NOW(), NULL
        );
      EOD;

      $this->db->query($fieldsData);
  }

  public function down()
  {
      $fieldsData = <<<EOD
          DELETE FROM
            custom_fields
          WHERE
            machine_name = 'activeAffiliation'
            AND
            model_name = 'People';

          UPDATE
            custom_fields cf
          SET
            "order" = cf."order" - 1
          FROM
            custom_field_groups
          WHERE
            cf.group_id = custom_field_groups.id
            AND
            custom_field_groups.machine_name = 'affiliation'
            AND
            cf."order" > 1;
      EOD;

      $this->db->query($fieldsData);
  }
}
