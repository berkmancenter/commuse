<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use App\Libraries\SystemSettingsWrapper;

class AddNewSystemSettingsRelatedToDataAudit extends Migration
{
  public function up()
  {
      $settings = SystemSettingsWrapper::getInstance()->getSettings();

      $settings['DataAuditUserEmailAcceptedSubject'] = [
        'type' => 'string',
        'value' => 'Automated message from the Commuse Portal regarding your recent profile changes.',
      ];

      $settings['DataAuditUserEmailAcceptedBody'] = [
        'type' => 'long_text_rich',
        'value' => <<<EOD
Thank you for updating your profile in the Commuse Portal. This is an automated message that was triggered by the recent changes you made. No actions need to be taken by you at this time.<br><br>
The changes you made to your profile have been accepted by the admin.
EOD,
      ];

      $settings['DataAuditUserEmailDeclinedSubject'] = [
        'type' => 'string',
        'value' => 'Automated message from the Commuse Portal regarding your recent profile changes.',
      ];

      $settings['DataAuditUserEmailDeclinedBody'] = [
        'type' => 'long_text_rich',
        'value' => <<<EOD
Thank you for updating your profile in the Commuse Portal. This is an automated message that was triggered by the recent changes you made. No actions need to be taken by you at this time.<br><br>
The changes you made to your profile have been declined by the admin.
EOD,
      ];

      SystemSettingsWrapper::getInstance()->saveSettings($settings);
  }

  public function down()
  {
      $settings = SystemSettingsWrapper::getInstance()->getSettings();
      unset($settings['DataAuditUserEmailAcceptedSubject']);
      unset($settings['DataAuditUserEmailAcceptedBody']);
      unset($settings['DataAuditUserEmailDeclinedSubject']);
      unset($settings['DataAuditUserEmailDeclinedBody']);
      SystemSettingsWrapper::getInstance()->saveSettings($settings);
  }
}
