<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNewFieldsToPeopleTable extends Migration
{
    private $fields = [
        'prefix' => [
            'type'       => 'VARCHAR',
            'constraint' => '50',
            'default'    => '',
        ],
        'middle_name' => [
            'type'       => 'VARCHAR',
            'constraint' => '100',
            'default'    => '',
        ],
        'preferred_name' => [
            'type'       => 'VARCHAR',
            'constraint' => '100',
            'default'    => '',
        ],
        'preferred_pronouns' => [
            'type'       => 'VARCHAR',
            'constraint' => '20',
            'default'    => '',
        ],
        'email' => [
            'type'       => 'VARCHAR',
            'constraint' => '200',
            'default'    => '',
        ],
        'mobile_phone_number' => [
            'type'       => 'VARCHAR',
            'constraint' => '30',
            'default'    => '',
        ],
        'affiliation' => [
            'type'       => 'JSONB',
            'default'    => '[]',
        ],
        'affiliation_years' => [
            'type'       => 'JSONB',
            'default'    => '[]',
        ],
        'interested_in' => [
            'type'       => 'JSONB',
            'default'    => '[]',
        ],
        'knowledgeable_in' => [
            'type'       => 'JSONB',
            'default'    => '[]',
        ],
        'working_groups' => [
            'type'       => 'JSONB',
            'default'    => '[]',
        ],
        'projects' => [
            'type'       => 'JSONB',
            'default'    => '[]',
        ],
        'website_link' => [
            'type'       => 'VARCHAR',
            'constraint' => '1000',
            'default'    => '',
        ],
        'twitter_link' => [
            'type'       => 'VARCHAR',
            'constraint' => '1000',
            'default'    => '',
        ],
        'linkedin_link' => [
            'type'       => 'VARCHAR',
            'constraint' => '1000',
            'default'    => '',
        ],
        'facebook_link' => [
            'type'       => 'VARCHAR',
            'constraint' => '1000',
            'default'    => '',
        ],
        'mastodon_link' => [
            'type'       => 'VARCHAR',
            'constraint' => '1000',
            'default'    => '',
        ],
        'instagram_link' => [
            'type'       => 'VARCHAR',
            'constraint' => '1000',
            'default'    => '',
        ],
        'snapchat_link' => [
            'type'       => 'VARCHAR',
            'constraint' => '1000',
            'default'    => '',
        ],
        'other_link' => [
            'type'       => 'VARCHAR',
            'constraint' => '1000',
            'default'    => '',
        ],
        'home_city' => [
            'type'       => 'VARCHAR',
            'constraint' => '100',
            'default'    => '',
        ],
        'home_state_province' => [
            'type'       => 'VARCHAR',
            'constraint' => '100',
            'default'    => '',
        ],
        'home_country' => [
            'type'       => 'VARCHAR',
            'constraint' => '100',
            'default'    => '',
        ],
        'employer_name' => [
            'type'       => 'VARCHAR',
            'constraint' => '100',
            'default'    => '',
        ],
        'job_title' => [
            'type'       => 'VARCHAR',
            'constraint' => '100',
            'default'    => '',
        ],
        'industry' => [
            'type'       => 'VARCHAR',
            'constraint' => '100',
            'default'    => '',
        ],
    ];

    public function up()
    {
        $this->forge->addColumn('people', $this->fields);
    }

    public function down()
    {
        $this->forge->dropColumn('people', ['prefix', 'middle_name', 'preferred_pronouns', 'email', 'mobile_phone_number']);
    }
}
