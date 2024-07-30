<?php
namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;

class DataAudit extends BaseController
{
  use ResponseTrait;

  /**
   * Retrieves profile data audit records.
   * Fetches audit records from the database with associated user information.
   * Allows filtering based on reintake, review status, and audit ID.
   * Returns the audit records as a JSON response.
   *
   * @return \CodeIgniter\HTTP\Response
   */
  public function profileDataAudit()
  {
    $this->checkAdminAccess();

    $request = \Config\Services::request();
    $db = \Config\Database::connect();
    $builder = $db->table('audit au');

    $builder
      ->select('
        pa.first_name AS audited_first_name,
        pa.last_name AS audited_last_name,
        pch.first_name AS changed_by_first_name,
        pch.last_name AS changed_by_last_name,
        au.id,
        au.changes,
        au.created_at,
        au.review
      ')
      ->join('people pa', 'pa.user_id = au.audited_id', 'left')
      ->join('people pch', 'pch.user_id = au.changed_user_id', 'left');

    if ($request->getGet('justReintake') == 'true') {
      $builder->where('(au.changes::text) LIKE \'%reintake%\'');
    }

    if ($request->getGet('justReview') == 'true') {
      $builder->where('au.review', 'required');
    }

    $profileAuditItems = $builder
      ->orderBy('created_at', 'DESC')
      ->get()
      ->getResultArray();

    $profileAuditItems = array_map(function($auditItem) {
      $auditItem['changes'] = json_decode($auditItem['changes']);
      return $auditItem;
    }, $profileAuditItems);

    return $this->respond($profileAuditItems);
  }

  /**
   * Processes an audit record based on the provided request data.
   * Determines the action to be taken (accept or deny) and whether a user email should be sent.
   *
   * @return \CodeIgniter\HTTP\Response
   */
  public function auditRecordProcess() {
    $requestData = $this->request->getJSON(true);
    $userEmail = false;

    if ($requestData['action'] === 'process_send_email') {
      $userEmail = true;
    }

    $decision = $requestData['decision'];

    if ($decision === 'accept') {
      $result = $this->auditRecordAccept($userEmail, $requestData['emailTemplate']);
    } else if ($decision === 'deny') {
      $result = $this->auditRecordAcceptRequestChanges($userEmail, $requestData['emailTemplate']);
    }

    return $this->respond([
      'message' => $result,
    ]);
  }

  /**
   * Accepts an audit record and updates a remote resource.
   * Sets the review status of the audit record to 'accepted'.
   * If remote sync URL and access token are provided, updates the remote resource.
   * Returns a JSON response indicating the acceptance and remote update status.
   *
   * @return string
   */
  private function auditRecordAccept($userEmail = false, $emailTemplate = null)
  {
    $this->checkAdminAccess();

    $request = \Config\Services::request();
    $auditId = $request->getJsonVar('id');

    $this->auditRecordReviewSet($auditId, 'accepted');

    $remoteSyncUrl = $_ENV['people.remoteSyncUrl'];
    $remoteSyncAccessToken = $_ENV['people.remoteSyncAccessToken'];

    if ($remoteSyncUrl && $remoteSyncAccessToken) {
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

        $curlOptions = [];
        if ($_ENV['CI_ENVIRONMENT'] === 'development') {
          $curlOptions['verify'] = false;
        }

        $client = \Config\Services::curlrequest($curlOptions);

        $headers = [
          'Access-Token' => $remoteSyncAccessToken,
          'Content-Type' => 'application/json',
        ];

        try {
          $client->post($remoteSyncUrl, [
            'headers' => $headers,
            'json' => $userData,
          ]);
        } catch (\Exception $e) {
          return 'Audit record has been accepted, but a remote resource couldn\'t be found.';
        }
      }
    }

    if ($userEmail) {
      $this->sendUserEmail($auditId, $emailTemplate);
    }

    return 'Audit record has been accepted and a remote resource has been updated.';
  }

  /**
   * Accepts an audit record and requests changes.
   * Sets the review status of the audit record to 'request_changes'.
   * Returns a JSON response indicating the acceptance of the request.
   *
   * @return string
   */
  private function auditRecordAcceptRequestChanges($userEmail = false, $emailTemplate = null)
  {
    $this->checkAdminAccess();

    $request = \Config\Services::request();
    $auditId = $request->getJsonVar('id');

    $this->auditRecordReviewSet($auditId, 'request_changes');

    if ($userEmail) {
      $this->sendUserEmail($auditId, $emailTemplate);
    }

    return 'Audit record has been updated with a changes request.';
  }

  private function sendUserEmail($auditId, $emailTemplate)
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
        return;
      }

      $email = \Config\Services::email();
      $email->setTo($userData['email']);
      $email->setSubject($emailTemplate['subject']);
      $email->setMessage($emailTemplate['body']);

      $email->send();
    }
  }

  /**
   * Sets the review status of an audit record.
   * Updates the 'review' column of the audit record with the provided status.
   *
   * @param int $id The ID of the audit record.
   * @param string $status The review status to set.
   * @return void
   */
  private function auditRecordReviewSet($id, $status)
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
}
