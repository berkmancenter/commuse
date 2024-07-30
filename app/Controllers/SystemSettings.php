<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Libraries\SystemSettingsWrapper;

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
    $this->checkAdminAccess();

    $requestData = $this->request->getJSON(true);

    SystemSettingsWrapper::getInstance()->saveSettings($requestData);

    return $this->respond(['message' => 'System settings have been saved.'], 200);
  }

    /**
   * Get data audit email templates.
   *
   * @return \CodeIgniter\HTTP\Response
   */
  public function getDataAuditEmailTemplates()
  {
    $this->checkAdminAccess();

    $settings = SystemSettingsWrapper::getInstance()->getSettings();
    $settings = array_filter($settings, function ($key) {
      return strpos($key, 'DataAuditUserEmail') !== false;
    }, ARRAY_FILTER_USE_KEY);

    return $this->respond($settings, 200);
  }
}
