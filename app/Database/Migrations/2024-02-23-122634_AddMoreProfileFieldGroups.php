<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMoreProfileFieldGroups extends Migration
{
    public function up()
    {
        $defaultGroupsData = [
          [
              'machine_name' => 'location_information',
              'title' => 'Location Information (Permanent Residence)',
              'order' => 3,
          ],
          [
              'machine_name' => 'location_current',
              'title' => 'Current Location',
              'order' => 4,
          ],
          [
              'machine_name' => 'affiliation',
              'title' => 'Affiliation',
              'order' => 5,
          ],
          [
              'machine_name' => 'online_presence',
              'title' => 'Online Presence',
              'order' => 6,
          ],
          [
              'machine_name' => 'educational_information',
              'title' => 'Educational Information',
              'order' => 7,
          ],
          [
              'machine_name' => 'professional_information',
              'title' => 'Professional Information',
              'order' => 8,
          ],
          [
              'machine_name' => 'current_work',
              'title' => 'Current Work',
              'order' => 9,
          ],
          [
              'machine_name' => 'current_knowledge',
              'title' => 'Current Knowledge',
              'order' => 10,
          ],
          [
              'machine_name' => 'interested_in',
              'title' => 'Interested In',
              'order' => 11,
          ],
          [
              'machine_name' => 'working_groups_projects',
              'title' => 'Working Groups & Projects',
              'order' => 12,
          ],
          [
              'machine_name' => 'favorites',
              'title' => 'Favorites',
              'order' => 13,
          ],
        ];

        $this->db->table('custom_field_groups')->insertBatch($defaultGroupsData);
    }

    public function down()
    {}
}
