<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use App\Libraries\SystemSettingsWrapper;

class AddSystemSettingsRelatedToZoomScheduler extends Migration
{
  public function up()
  {
    $settings = SystemSettingsWrapper::getInstance()->getSettings();

    $settings['ZoomSchedulerAccountId'] = [
      'type' => 'string',
      'title' => 'Zoom scheduler account id',
      'value' => '',
    ];

    $settings['ZoomSchedulerClientId'] = [
      'type' => 'string',
      'title' => 'Zoom scheduler client id',
      'value' => '',
    ];

    $settings['ZoomSchedulerClientSecret'] = [
      'type' => 'string',
      'title' => 'Zoom scheduler client secret',
      'value' => '',
    ];

    service('settings')->set('SystemSettings.settings', json_encode($settings));
  }

  public function down()
  {
    $settings = SystemSettingsWrapper::getInstance()->getSettings();
    unset($settings['ZoomSchedulerAccountId']);
    unset($settings['ZoomSchedulerClientId']);
    unset($settings['ZoomSchedulerClientSecret']);
    service('settings')->set('SystemSettings.settings', json_encode($settings));
  }
}
