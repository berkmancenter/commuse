<?php

namespace App\Libraries;

use CodeIgniter\Cache\CacheInterface;
use App\Libraries\Cache;

class SystemSettingsWrapper
{
  private static $instance = null;
  protected $cache;
  protected $settingsService;
  protected $cacheKey = 'system_settings';
  protected $cacheExpiration;

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
   */
  public function saveSettings(array $settings)
  {
    $settingsJson = json_encode($settings);

    $this->settingsService->set('SystemSettings.settings', $settingsJson);
    $this->cache->save($this->cacheKey, $settingsJson, $this->cacheExpiration);
  }
}
