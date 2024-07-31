<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use App\Libraries\SystemSettingsWrapper;

class MovePublicProfileWarningTextToSettings extends Migration
{
    public function up()
    {
        $settings = SystemSettingsWrapper::getInstance()->getSettings();

        $settings['PublicProfileWarningInAccountSettings'] = [
          'type' => 'string',
          'value' => 'Your profile is currently set to private and will not show in the people page. To allow users of this platform to view your profile, please check the "Make my profile visible to other users of the portal" checkbox.',
        ];

        $settings['PublicProfileWarningShowProfile'] = [
          'type' => 'string',
          'value' => ' Your profile is not public, currently it\'s visible only to you. To make it public, please check the "Make my profile visible to other users of the portal" checkbox on the "Account settings" page.',
        ];

        $settings['PublicProfileCheckboxLabel'] = [
          'type' => 'string',
          'value' => 'Make my profile visible to other users of the portal',
        ];

        SystemSettingsWrapper::getInstance()->saveSettings($settings);
    }

    public function down()
    {
        $settings = SystemSettingsWrapper::getInstance()->getSettings();
        unset($settings['PublicProfileWarningInAccountSettings']);
        unset($settings['PublicProfileWarningShowProfile']);
        unset($settings['PublicProfileCheckboxLabel']);
        SystemSettingsWrapper::getInstance()->saveSettings($settings);
    }
}
