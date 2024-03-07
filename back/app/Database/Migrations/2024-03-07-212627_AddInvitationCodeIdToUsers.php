<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddInvitationCodeIdToUsers extends Migration
{
  public function up()
  {
    $fields = [
      'invitation_code_id' => [
        'type' => 'INT',
        'unsigned' => true,
        'null' => true,
      ],
    ];

    $this->forge->addColumn('users', $fields);
  }

  public function down()
  {
    $this->forge->dropColumn('users', ['used_by']);
  }
}
