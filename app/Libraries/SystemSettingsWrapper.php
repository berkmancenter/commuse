<?php

namespace App\Libraries;

use CodeIgniter\Cache\CacheInterface;
use App\Libraries\Cache;
use JsonSchema\Validator;

class SystemSettingsWrapper
{
  private static $instance = null;
  protected $cache;
  protected $settingsService;
  protected $cacheKey = 'system_settings';
  protected $cacheExpiration;

  public static $publicSettings = [
    'PublicProfileWarningInAccountSettings',
    'PublicProfileWarningShowProfile',
    'PublicProfileCheckboxLabel',
    'DataAuditUserEmailAcceptedSubject',
    'DataAuditUserEmailAcceptedBody',
    'DataAuditUserEmailDeclinedSubject',
    'DataAuditUserEmailDeclinedBody',
    'SystemEnabledModules',
  ];

  private function __construct(CacheInterface $cache, $settingsService)
  {
    $this->cache = $cache;
    $this->settingsService = $settingsService;
    $this->cacheExpiration = Cache::$defaultCacheExpiration;
  }

  /**
   * Get the singleton instance of the SystemSettingsWrapper.
   *
   * @return SystemSettingsWrapper The singleton instance.
   */
  public static function getInstance()
  {
    if (self::$instance === null) {
      self::$instance = new self(\Config\Services::cache(), service('settings'));
    }
    return self::$instance;
  }

  /**
   * Get all settings.
   *
   * @return mixed The settings data.
   */
  public function getSettings()
  {
    $cachedData = $this->cache->get($this->cacheKey);

    if ($cachedData && Cache::isCacheEnabled()) {
      return json_decode($cachedData, true);
    }

    $settings = json_decode($this->settingsService->get('SystemSettings.settings'), true);

    $this->cache->save($this->cacheKey, json_encode($settings), $this->cacheExpiration);

    return $settings;
  }

  /**
   * Get a specific setting by key.
   *
   * @param string $key The key of the setting.
   * @return mixed The setting value.
   */
  public function getSettingByKey(string $key)
  {
    $settings = $this->getSettings();
    return $settings[$key] ?? null;
  }

  /**
   * Save the settings.
   *
   * @param array $settings The settings data to save.
   * @return bool True if the settings were saved successfully, false otherwise.
   */
  public function saveSettings(array $settings)
  {
    $settingsObject = json_decode(json_encode($settings));

    // Perform JSON Schema validation
    $validator = new Validator;
    $validator->validate($settingsObject, (object)[
      '$ref' => 'file://' . APPPATH . 'Schemas/system_settings_schema.json'
    ]);

    if ($validator->isValid()) {
      $settingsJson = json_encode($settings);

      $this->settingsService->set('SystemSettings.settings', $settingsJson);
      $this->cache->save($this->cacheKey, $settingsJson, $this->cacheExpiration);

      return true;
    } else {
      $errors = $validator->getErrors();
      log_message('error', 'System settings validation failed: ' . json_encode($errors));

      return false;
    }
  }

  /**
   * Check if a specific value is in the array of a setting.
   * 
   * @param string $key The key of the setting.
   * @param string $lookedKey The key to look for in the array.
   * @return bool True if the value is in the array, false otherwise.
   */
  public function isValueInArray(string $lookedKey, string $key)
  {
    $settings = $this->getSettingByKey($key);
    if (isset($settings['value']) && is_array($settings['value'])) {
      foreach ($settings['value'] as $setting) {
        if ($setting['id'] == $lookedKey) {
          return true;
        }
      }
    }

    return false;
  }
}
