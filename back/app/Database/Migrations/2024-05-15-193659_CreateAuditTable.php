<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuditTable extends Migration
{
    public function up()
    {
      $this->forge->addField([
          'id' => [
              'type' => 'INT',
              'unsigned' => true,
              'auto_increment' => true,
          ],
          'audited_id' => [
              'type' => 'INT',
              'unsigned' => true,
              'null' => false,
          ],
          'changed_user_id' => [
              'type' => 'INT',
              'unsigned' => true,
              'null' => false,
          ],
          'model_name' => [
              'type' => 'VARCHAR',
              'constraint' => 50,
              'null' => false,
          ],
          'changes' => [
              'type' => 'JSONB',
              'default' => '[]',
          ],
          'created_at timestamp default current_timestamp',
      ]);

      $this->forge->addPrimaryKey('id');
      $this->forge->createTable('audit');
    }

    public function down()
    {
        $this->forge->dropTable('audit');
    }
}
