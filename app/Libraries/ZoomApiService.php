<?php

namespace App\Libraries;

use App\Libraries\SystemSettingsWrapper;

class ZoomApiService {

  /**
   * Zoom API token endpoint (client credentials flow)
   * @var string
   */
  private $tokenEndpoint = 'https://zoom.us/oauth/token?grant_type=account_credentials&account_id=';

  /**
   * The Zoom client ID.
   * @var string
   */
  private $clientId;

  /**
   * The Zoom client secret.
   * @var string
   */
  private $clientSecret;

  /**
   * Constructor - initializes Zoom credentials.
   */
  public function __construct() {
    $settings = SystemSettingsWrapper::getInstance();
    $this->clientId     = $settings->getSettingByKey('ZoomSchedulerClientId')['value'];
    $this->clientSecret = $settings->getSettingByKey('ZoomSchedulerClientSecret')['value'];
    $this->tokenEndpoint .= $settings->getSettingByKey('ZoomSchedulerAccountId')['value'];
  }

  /**
   * Executes a cURL request with the given parameters.
   *
   * @param string $url The endpoint URL.
   * @param string $method The HTTP method (GET, POST, etc.).
   * @param array $headers Array of HTTP headers.
   * @param mixed $payload (Optional) Data to send with the request.
   * @return array Associative array with keys 'data' and 'code' (or 'error' in case of failure).
   */
  private function executeCurlRequest(string $url, string $method, array $headers, $payload = null): array {
    $ch = curl_init($url);

    $options = [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTPHEADER     => $headers,
    ];

    // Set HTTP method and payload if provided.
    switch (strtoupper($method)) {
      case 'POST':
        $options[CURLOPT_POST] = true;
        if ($payload !== null) {
          $options[CURLOPT_POSTFIELDS] = $payload;
        }
        break;
      case 'PUT':
      case 'PATCH':
      case 'DELETE':
        $options[CURLOPT_CUSTOMREQUEST] = strtoupper($method);
        if ($payload !== null) {
          $options[CURLOPT_POSTFIELDS] = $payload;
        }
        break;
      default:
        // GET is the default.
        break;
    }

    curl_setopt_array($ch, $options);

    $result = curl_exec($ch);
    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
      $error = curl_error($ch);
      curl_close($ch);
      return [
        'error' => "cURL error: $error"
      ];
    }
    curl_close($ch);

    $data = json_decode($result, true);

    // If status code is error, pass the message if available.
    if ($httpStatus >= 400) {
      $errorMessage = isset($data['message']) ? $data['message'] : 'Unknown error occurred';
      return [
        'error' => $errorMessage,
        'code'  => $httpStatus
      ];
    }

    return [
      'data' => $data,
      'code' => $httpStatus
    ];
  }

  /**
   * Retrieves an access token from Zoom using OAuth2 (client credentials flow).
   *
   * @return string|null The access token, or null on failure.
   */
  public function getAccessToken() {
    if (empty($this->clientId) || empty($this->clientSecret)) {
      log_message('error', 'Zoom API credentials are not properly configured.');
      return null;
    }

    $basicAuth = base64_encode($this->clientId . ':' . $this->clientSecret);
    $headers = [
      'Authorization: Basic ' . $basicAuth,
      'Content-Type: application/x-www-form-urlencoded'
    ];

    $response = $this->executeCurlRequest($this->tokenEndpoint, 'POST', $headers);
    if (isset($response['error'])) {
      log_message('error', 'Zoom token endpoint error: ' . $response['error']);
      return null;
    }

    $data = $response['data'];
    return $data['access_token'] ?? null;
  }

  /**
   * Creates a meeting using the Zoom API.
   *
   * @param array $meetingData Data required for the meeting creation.
   * @return array The API response or an error array on failure.
   */
  public function createMeeting(array $meetingData) {
    $accessToken = $this->getAccessToken();
    if (!$accessToken) {
      return [
        'error' => 'Unable to obtain Zoom access token.'
      ];
    }

    $zoomEndpoint = "https://api.zoom.us/v2/users/me/meetings";
    $headers = [
      'Authorization: Bearer ' . $accessToken,
      'Content-Type: application/json',
      'Accept: application/json',
    ];

    $payload = json_encode($meetingData);
    $response = $this->executeCurlRequest($zoomEndpoint, 'POST', $headers, $payload);
    if (isset($response['error'])) {
      return [
        'error' => 'Zoom API error: ' . $response['error']
      ];
    }

    return $response['data'];
  }

  /**
   * Gets a list of all meetings scheduled for the current user.
   *
   * @return array The list of meetings or an error array on failure.
   */
  public function getMeetings() {
    $accessToken = $this->getAccessToken();
    if (!$accessToken) {
      return [
        'error' => 'Unable to obtain Zoom access token.'
      ];
    }

    $zoomEndpoint = 'https://api.zoom.us/v2/users/me/meetings';
    $headers = [
      'Authorization: Bearer ' . $accessToken,
      'Content-Type: application/json',
      'Accept: application/json',
    ];

    $response = $this->executeCurlRequest($zoomEndpoint, 'GET', $headers);
    if (isset($response['error'])) {
      return [
        'error' => 'Zoom API error: ' . $response['error']
      ];
    }

    return $response['data'];
  }
}
