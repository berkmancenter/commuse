<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBuzzLikesTable extends Migration
{
  public function up()
  {
    $this->forge->addField([
      'id' => [
        'type'           => 'INT',
        'unsigned'       => true,
        'auto_increment' => true,
      ],
      'buzz_id' => [
        'type'           => 'INT',
        'unsigned'       => true,
      ],
      'user_id' => [
        'type'           => 'INT',
        'unsigned'       => true,
      ],
      'created_at' => [
        'type'           => 'DATETIME',
        'null'           => true,
      ],
    ]);

    $this->forge->addKey('id', true);
    $this->forge->addForeignKey('buzz_id', 'buzz', 'id', 'CASCADE', 'CASCADE');
    $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
    $this->forge->createTable('buzz_likes');
  }

  public function down()
  {
    $this->forge->dropTable('buzz_likes');
  }
}
