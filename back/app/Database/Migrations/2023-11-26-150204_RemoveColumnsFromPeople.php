<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveColumnsFromPeople extends Migration
{
    public function up()
    {
        $columnsToKeep = ['id', 'prefix', 'first_name', 'middle_name', 'last_name', 'preferred_pronouns', 'image_url', 'bio', 'user_id', 'public_profile', 'created_at', 'updated_at', 'mobile_phone_number', 'email'];
        $tableName = 'people';
        $tableFields = $this->db->getFieldNames($tableName);

        $columnsToRemove = array_diff($tableFields, $columnsToKeep);

        foreach ($columnsToRemove as $column) {
            $this->forge->dropColumn($tableName, $column);
        }
    }

    public function down()
    {}
}
