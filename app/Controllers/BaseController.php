<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *   class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
  /**
   * Instance of the main Request object.
   *
   * @var CLIRequest|IncomingRequest
   */
  protected $request;

  /**
   * An array of helpers to be loaded automatically upon
   * class instantiation. These helpers will be available
   * to all other controllers that extend BaseController.
   *
   * @var array
   */
  protected $helpers = [];

  /**
   * Constructor.
   *
   * @param RequestInterface  $request
   * @param ResponseInterface $response
   * @param LoggerInterface   $logger
   */
  public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
  {
    // Do Not Edit This Line
    parent::initController($request, $response, $logger);
  }

  /**
   * Maps request data to the specified keys.
   *
   * @param array $requestData The request data.
   * @param array $keys        The keys to map the data to.
   *
   * @return array The mapped data.
   */
  protected function mapRequestData($requestData, $keys)
  {
    $mappedData = [];

    foreach ($keys as $key) {
      $mappedData[$key] = $requestData[$key] ?? '';
    }

    return $mappedData;
  }

  /**
   * Converts a date string to a milliseconds timestamp.
   *
   * @param string $dateString The date string to convert.
   *
   * @return int The milliseconds timestamp.
   */
  protected function dateToMillisecondsTimestamp($dateString)
  {
    return strtotime($dateString) * 1000;
  }

  /**
   * Sends an authentication error response.
   *
   * @return void
   */
  protected function authError()
  {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['message' => 'Unauthorized.']);
    exit();
  }

  /**
   * Checks if the user has admin access.
   * If not, sends an authentication error response.
   *
   * @return void
   */
  protected function checkAdminAccess()
  {
    if (auth()->user()->can('admin.access') === false) {
      $this->authError();
    }
  }
}
