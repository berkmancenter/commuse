<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use App\Libraries\SystemSettingsWrapper;

class MoveDataAuditEmailsEnvToSettings extends Migration
{
    public function up()
    {
        $settings = SystemSettingsWrapper::getInstance()->getSettings();
        $settings['DataAuditReviewAdminEmails'] = [
          'type' => 'string',
          'value' => 'admin@example.com',
        ];
        SystemSettingsWrapper::getInstance()->saveSettings($settings);
    }

    public function down()
    {
      $settings = SystemSettingsWrapper::getInstance()->getSettings();
      unset($settings['DataAuditReviewAdminEmails']);
      SystemSettingsWrapper::getInstance()->saveSettings($settings);
    }
}
