<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;

class DataAudit extends BaseController
{
  use ResponseTrait;

  public function profileDataAudit() {
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

    if ($request->getGet('auditId') != 'false') {
      $builder->where('au.id', $request->getGet('auditId'));
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

  public function auditRecordAccept() {
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
          $response = $client->post($remoteSyncUrl, [
            'headers' => $headers,
            'json' => $userData,
          ]);
        } catch (\Exception $e) {
          return $this->respond([
            'message' => 'Audit record has been accepted, but a remote resource couldn\'t be found.'
          ]);
        }
      }
    }

    return $this->respond([
      'message' => 'Audit record has been accepted and a remote resource has been updated.'
    ]);
  }

  public function auditRecordAcceptRequestChanges() {
    $this->checkAdminAccess();

    $request = \Config\Services::request();
    $auditId = $request->getJsonVar('id');

    $this->auditRecordReviewSet($auditId, 'request_changes');

    return $this->respond(['ok']);
  }

  private function auditRecordReviewSet($id, $status) {
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
