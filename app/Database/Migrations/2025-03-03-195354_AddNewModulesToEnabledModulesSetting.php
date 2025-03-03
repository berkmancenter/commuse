<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use App\Libraries\SystemSettingsWrapper;

class AddNewModulesToEnabledModulesSetting extends Migration
{
  public function up()
  {
    $settings = SystemSettingsWrapper::getInstance()->getSettings();
    $listOfNewModules = $this->getListOfNewModules();

    foreach ($listOfNewModules as $id => $title) {
      $newModule = [
        'id' => $id,
        'title' => $title,
      ];

      $settings['SystemEnabledModules']['options'][] = $newModule;
      $settings['SystemEnabledModules']['value'][] = $newModule;
    }

    service('settings')->set('SystemSettings.settings', json_encode($settings));
  }

  public function down()
  {
    $settings = SystemSettingsWrapper::getInstance()->getSettings();
    $listOfNewModules = $this->getListOfNewModules();

    $settings['SystemEnabledModules']['options'] = array_filter(
      $settings['SystemEnabledModules']['options'],
      fn($module) => !in_array($module['id'], array_keys($listOfNewModules))
    );

    $settings['SystemEnabledModules']['value'] = array_filter(
      $settings['SystemEnabledModules']['value'],
      fn($module) => !in_array($module['id'], array_keys($listOfNewModules))
    );

    service('settings')->set('SystemSettings.settings', json_encode($settings));
  }

  private function getListOfNewModules()
  {
    return [
      'news' => 'News',
      'people' => 'People',
      'invitations' => 'Invitations',
      'custom_fields' => 'Custom fields',
      'data_editor' => 'Data editor',
      'data_audit' => 'Data audit',
    ];
  }
}
