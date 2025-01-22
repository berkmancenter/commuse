<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Libraries\SystemSettingsWrapper;
use App\Libraries\Cache;

/**
 * This class is responsible for handling system settings-related operations.
 */
class SystemSettingsController extends BaseController
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
    $this->checkAdminAccess();

    $cache = \Config\Services::cache();

    $requestData = $this->request->getJSON(true);

    $settingsWrapper = SystemSettingsWrapper::getInstance();
    $saveResult = $settingsWrapper->saveSettings($requestData);

    if ($saveResult === true) {
      $cache->delete('publis_system_settings');
      return $this->respond([
        'success' => true,
        'message' => 'System settings have been saved successfully.'
      ], 200);
    } else {
      return $this->respond([
        'success' => false,
        'message' => 'Failed to save system settings due to validation errors.',
      ], 400);
    }
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
