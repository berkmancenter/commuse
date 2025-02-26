<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use App\Libraries\SystemSettingsWrapper;

class AddZoomSchedulerModuleOptionToSettings extends Migration
{
  public function up()
  {
    $settings = SystemSettingsWrapper::getInstance()->getSettings();

    $newModule = [
      'id' => 'zoom_scheduler',
      'title' => 'Zoom scheduler',
    ];

    if (!in_array($newModule, $settings['SystemEnabledModules']['options'])) {
      $settings['SystemEnabledModules']['options'][] = $newModule;
    }

    service('settings')->set('SystemSettings.settings', json_encode($settings));
  }

  public function down()
  {
    $settings = SystemSettingsWrapper::getInstance()->getSettings();

    $settings['SystemEnabledModules']['options'] = array_filter(
      $settings['SystemEnabledModules']['options'],
      fn($module) => $module['id'] !== 'zoom_scheduler'
    );

    service('settings')->set('SystemSettings.settings', json_encode($settings));
  }
}
