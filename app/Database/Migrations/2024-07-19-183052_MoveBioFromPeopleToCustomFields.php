<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MoveBioFromPeopleToCustomFields extends Migration
{
    public function up()
    {
        $fieldsData = <<<EOD
          UPDATE
            custom_fields
          SET
            "order" = "order" + 1
          WHERE
            group_id = 1;

          INSERT INTO custom_fields (
              input_type, machine_name, model_name, group_id, "order", title, description, metadata, created_at, updated_at, parent_field_id
          ) VALUES (
              'long_text', 'bio', 'People', 1, 1, 'Bio', '', '{}', NOW(), NOW(), NULL
          );

          DROP INDEX IF EXISTS custom_field_data_value;
          CREATE INDEX custom_field_data_value_fulltext ON custom_field_data USING gin (to_tsvector('english', value));

          INSERT INTO custom_field_data (
              custom_field_id,
              model_id,
              value,
              created_at,
              updated_at
          )
          SELECT
              (SELECT id FROM custom_fields WHERE title = 'Bio')  AS custom_field_id,
              p.id AS model_id,
              p.bio AS value,
              NOW() AS created_at,
              NOW() AS updated_at
          FROM 
              people p;

          ALTER TABLE
            people
          DROP COLUMN
            bio;
        EOD;

        $this->db->query($fieldsData);
    }

    public function down()
    {
        $fieldsData = <<<EOD
            ALTER TABLE
              people
            ADD COLUMN
              bio text;

            UPDATE
              people p
            SET bio = (
                SELECT cfd.value
                FROM custom_field_data cfd
                JOIN custom_fields cf ON cf.id = cfd.custom_field_id
                WHERE cf.title = 'Bio' AND cfd.model_id = p.id
            );

            DELETE FROM
              custom_field_data
            WHERE
              custom_field_id = (SELECT id FROM custom_fields WHERE title = 'Bio');

            DELETE FROM
              custom_fields
            WHERE
              title = 'Bio';

            DROP INDEX IF EXISTS
              custom_field_data_value_fulltext;

            CREATE INDEX custom_field_data_value ON custom_field_data(value);

            UPDATE custom_fields
            SET "order" = "order" - 1
            WHERE group_id = 1;
        EOD;

        $this->db->query($fieldsData);
    }
    
}
