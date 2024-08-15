<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdatePublicProfileDefault extends Migration
{
    public function up()
    {
      $this->db->query('ALTER TABLE "people" ALTER COLUMN "public_profile" SET DEFAULT TRUE');
    }

    public function down()
    {
      $this->db->query('ALTER TABLE "people" ALTER COLUMN "public_profile" SET DEFAULT FALSE');
    }
}
