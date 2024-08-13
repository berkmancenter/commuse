<?php

namespace App\Models;

use CodeIgniter\Model;

class DataAuditModel extends Model
{
  protected $DBGroup      = 'default';
  protected $table      = 'audit';
  protected $primaryKey     = 'id';
  protected $useAutoIncrement = true;
  protected $returnType     = 'array';
  protected $useSoftDeletes   = false;
  protected $protectFields  = false;
  // Dates
  protected $useTimestamps = true;
  protected $dateFormat  = 'datetime';
  protected $createdField  = 'created_at';
  protected $updatedField  = 'updated_at';
  protected $deletedField  = 'deleted_at';

  // Validation
  protected $skipValidation     = false;
  protected $cleanValidationRules = true;

  // Callbacks
  protected $allowCallbacks = true;

  /**
   * Processes the acceptance of an audit record and attempts to sync the corresponding user data
   * with a remote resource.
   *
   * This function marks the audit record with the specified ID as 'accepted' in the database.
   * If the remote sync URL and access token are configured, it retrieves the audited person's
   * profile data and attempts to send it to the remote service using a POST request.
   *
   * - Connects to the database and retrieves the audit record by ID.
   * - If the record exists and pertains to the 'People' model, retrieves the user's profile data.
   * - Sends the data to the remote sync URL using a cURL POST request with the required headers.
   * - Catches and handles any exceptions that occur during the remote request.
   *
   * @param int $auditId The ID of the audit record to be processed.
   * @return string A message indicating whether the audit record was accepted and the remote resource updated.
   */
  public function processAuditRecordAccept($auditId)
  {
    $this->auditRecordReviewSet($auditId, 'accepted');

    $db = \Config\Database::connect();
    $builder = $db->table('audit a');

    // Retrieve audit item from the database
    $auditItem = $builder
      ->where('a.id', $auditId)
      ->where('a.model_name', 'People')
      ->get()
      ->getResultArray();

    if ($auditItem = reset($auditItem)) {
      $personId = $auditItem['audited_id'];

      $usersModel = new UserModel();
      $userData = $usersModel->getUserProfileData($personId);

      // Synchronize user data with the remote service
      $syncResult = $this->syncUserData($userData);

      if (!$syncResult) {
        return 'Audit record has been accepted, but a remote resource couldn\'t be found.';
      }
    }

    return 'Audit record has been accepted and a remote resource has been updated.';
  }

  /**
   * Sets the review status of an audit record.
   * Updates the 'review' column of the audit record with the provided status.
   *
   * @param int $id The ID of the audit record.
   * @param string $status The review status to set.
   * @return void
   */
  public function auditRecordReviewSet($id, $status)
  {
    $db = \Config\Database::connect();
    $builder = $db->table('audit a');

    $builder
      ->where('a.id', $id)
      ->where('a.model_name', 'People');

    $builder->update([
      'review' => $status,
    ]);
  }

  /**
   * Sends an email to the user associated with the audited record.
   *
   * @param int $auditId The ID of the audit record.
   * @param array $emailTemplate The email template to use.
   * @return bool
   */
  public function sendUserEmail($auditId, $emailTemplate)
  {
    $db = \Config\Database::connect();
    $builder = $db->table('audit a');

    $auditItem = $builder
      ->where('a.id', $auditId)
      ->where('a.model_name', 'People')
      ->get()
      ->getResultArray();

    if ($auditItem = reset($auditItem)) {
      $personId = $auditItem['audited_id'];

      $usersModel = new UserModel();
      $userData = $usersModel->getUserProfileData($personId);

      if (!$userData['email']) {
        return false;
      }

      $email = \Config\Services::email();
      $email->setTo($userData['email']);
      $email->setSubject($emailTemplate['subject']);
      $email->setMessage($emailTemplate['body']);

      $email->send();

      return true;
    }

    return false;
  }

  /**
   * Syncs user data to a specified URL using the provided access token and user data.
   *
   * @param array $userData The user data to be synced.
   * @param string $url The URL to which the user data will be synced.
   * @param string $accessToken The access token used for authentication.
   * @return bool Returns true if the user data was successfully synced, false otherwise.
   */
  public function syncUserData($userData, $url = null, $accessToken = null)
  {
    // Set default values if not provided
    $url = $url ?? $_ENV['people.remoteSyncUrl'];
    $accessToken = $accessToken ?? $_ENV['people.remoteSyncAccessToken'];
    $curlOptions = [];
    $curlOptions['verify'] = false;

    $client = \Config\Services::curlrequest($curlOptions);

    $headers = [
      'Access-Token' => $accessToken,
      'Content-Type' => 'application/json',
    ];

    try {
      $client->post($url, [
        'headers' => $headers,
        'json' => $userData,
      ]);

      return true;
    } catch (\Exception $e) {
      return false;
    }
  }
}
