<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use App\Libraries\SystemSettingsWrapper;

class AddNewSystemSettingsRelatedToModules extends Migration
{
  public function up()
  {
    $settings = SystemSettingsWrapper::getInstance()->getSettings();

    $settings['SystemEnabledModules'] = [
      'type' => 'multi_select',
      'options' => [
        [
          'id' => 'buzz',
          'title' => 'Buzz',
        ],
        [
          'id' => 'people_map',
          'title' => 'People map',
        ],
      ],
      'value' => [
        [
          'id' => 'buzz',
          'title' => 'Buzz',
        ],
        [
          'id' => 'people_map',
          'title' => 'People map',
        ],
      ],
      'title' => 'System enabled modules',
    ];

    SystemSettingsWrapper::getInstance()->saveSettings($settings);
  }

  public function down()
  {
      $settings = SystemSettingsWrapper::getInstance()->getSettings();
      unset($settings['SystemEnabledModules']);
      SystemSettingsWrapper::getInstance()->saveSettings($settings);
  }
}
