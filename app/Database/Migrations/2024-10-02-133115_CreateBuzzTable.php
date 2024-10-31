<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBuzzTable extends Migration
{
  public function up()
  {
    $this->forge->addField([
        'id' => [
          'type'           => 'INT',
          'unsigned'       => true,
          'auto_increment' => true,
        ],
        'user_id' => [
          'type'           => 'INT',
          'unsigned'       => true,
        ],
        'content' => [
          'type'           => 'TEXT',
        ],
        'parent_id' => [
          'type'           => 'INT',
          'unsigned'       => true,
          'null'           => true,
          'default'        => null,
        ],
        'likes' => [
          'type'           => 'INT',
          'unsigned'       => true,
          'default'        => 0,
        ],
        'tags' => [
          'type'           => 'JSONB',
          'default'        => '[]',
        ],
        'created_at' => [
          'type'           => 'DATETIME',
          'null'           => true,
          'default'        => 'NOW()',
        ],
        'updated_at' => [
          'type'           => 'DATETIME',
          'null'           => true,
          'default'        => 'NOW()',
        ]
    ]);

    $this->forge->addKey('id', true);
    $this->forge->addForeignKey('parent_id', 'buzz', 'id', 'CASCADE', 'CASCADE');
    $this->forge->createTable('buzz');
  }

  public function down()
  {
    $this->forge->dropForeignKey('buzz', 'buzz_parent_id_foreign');
    $this->forge->dropTable('buzz');
  }
}
