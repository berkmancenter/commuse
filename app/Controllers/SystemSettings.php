<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Libraries\SystemSettingsWrapper;
use App\Libraries\Cache;

/**
 * This class is responsible for handling system settings-related operations.
 */
class SystemSettings extends BaseController
{
  use ResponseTrait;

  /**
   * Retrieves and returns a list of settings.
   *
   * @return \CodeIgniter\HTTP\ResponseInterface The response containing a list of system settings with their values.
   */
  public function index()
  {
    $this->checkAdminAccess();

    $settings = SystemSettingsWrapper::getInstance()->getSettings();

    return $this->respond($settings);
  }

  /**
   * Save the system settings.
   *
   * @return \CodeIgniter\HTTP\Response
   */
  public function saveSettings()
  {
    $cache = \Config\Services::cache();

    $this->checkAdminAccess();

    $requestData = $this->request->getJSON(true);

    SystemSettingsWrapper::getInstance()->saveSettings($requestData);

    $cache->delete('publis_system_settings');

    return $this->respond(['message' => 'System settings have been saved.'], 200);
  }

    /**
   * Get data audit email templates.
   *
   * @return \CodeIgniter\HTTP\Response
   */
  public function getPublicSettings()
  {
    $cache = \Config\Services::cache();
    $cachedData = $cache->get('publis_system_settings');

    if ($cachedData && Cache::isCacheEnabled()) {
      return $this->respond($cachedData);
    }

    $settings = SystemSettingsWrapper::getInstance()->getSettings();
    $settings = array_filter($settings, function ($key) {
      return in_array($key, SystemSettingsWrapper::$publicSettings);
    }, ARRAY_FILTER_USE_KEY);

    $cache->save('publis_system_settings', $settings, Cache::$defaultCacheExpiration);

    return $this->respond($settings, 200);
  }
}
