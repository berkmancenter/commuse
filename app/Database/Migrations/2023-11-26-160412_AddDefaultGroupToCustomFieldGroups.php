<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDefaultGroupsToCustomFieldGroups extends Migration
{
    public function up()
    {
        $defaultGroupsData = [
          [
              'machine_name' => 'my_information',
              'title' => 'My Information',
              'order' => 1,
          ],
          [
              'machine_name' => 'contact_information',
              'title' => 'Contact Information',
              'order' => 2,
          ]
        ];

        $this->db->table('custom_field_groups')->insertBatch($defaultGroupsData);
    }

    public function down()
    {}
}
