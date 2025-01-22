<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use App\Libraries\SystemSettingsWrapper;

class UpdateSettingsWithTitle extends Migration
{
  public function up()
  {
    $settingsWrapper = SystemSettingsWrapper::getInstance();
    $settings = $settingsWrapper->getSettings();

    // Iterate over each setting to add the 'title'
    foreach ($settings as $key => &$setting) {
      if (!isset($setting['title'])) {
        $setting['title'] = $this->generateTitleFromKey($key);
      }
    }

    // Save the updated settings
    service('settings')->set('SystemSettings.settings', json_encode($settings));
  }

  public function down()
  {
    $settingsWrapper = SystemSettingsWrapper::getInstance();
    $settings = $settingsWrapper->getSettings();

    // Remove the 'title' attribute from each setting
    foreach ($settings as &$setting) {
      unset($setting['title']);
    }

    // Save the settings without the 'title' attributes
    service('settings')->set('SystemSettings.settings', json_encode($settings));
  }

  /**
   * Generate a human-readable title from a setting key with the first letter capitalized.
   * 
   * @param string $key The setting key to generate a title from.
   * @return string The generated title.
   */
  private function generateTitleFromKey($key)
  {
    // Split the key by capital letters
    $title = preg_replace('/([a-z])([A-Z])/', '$1 $2', $key);

    // Capitalize the first letter of the resulting title
    $title = ucfirst(strtolower($title));

    return $title;
  }
}
