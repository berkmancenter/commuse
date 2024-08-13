<?php
namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;
use App\Models\DataAuditModel;

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

    $paginateCurrentPage = intval($this->request->getGet('paginateCurrentPage') ?? 1);
    $limitStart = ($paginateCurrentPage - 1) * 20;

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

    if ($request->getGet('query')) {
      $query = "'%" . $db->escapeLikeString($request->getGet('query')) . "%'";

      $builder->groupStart();

      $builder->where("
        au.changes::text ILIKE $query OR
        pa.first_name ILIKE $query OR
        pa.last_name ILIKE $query OR
        pch.first_name ILIKE $query OR
        pch.last_name ILIKE $query
      ");

      $builder->groupEnd();
    }

    if ($request->getGet('id')) {
      $builder->where('au.id', $request->getGet('id'));
    }

    if ($request->getGet('fields')) {
      $fields = explode(',', $request->getGet('fields'));

      $builder->groupStart();

      foreach ($fields as $field) {
        $fieldSanitized = preg_replace('/[^a-zA-Z0-9_]/', '', $field);

        $builder->groupStart();

        $builder->where("jsonb_path_exists(au.changes, '$.new.$fieldSanitized')");
        $builder->orWhere("jsonb_path_exists(au.changes, '$.old.$fieldSanitized')");

        $builder->groupEnd();
      }

      $builder->groupEnd();
    }

    $builderForCount = clone $builder;

    $profileAuditItems = $builder
      ->limit(20, $limitStart)
      ->orderBy('created_at', 'DESC')
      ->get()
      ->getResultArray();

    $profileAuditItemsCount = $builderForCount
      ->countAllResults();

    $profileAuditItems = array_map(function($auditItem) {
      $auditItem['changes'] = json_decode($auditItem['changes']);
      return $auditItem;
    }, $profileAuditItems);

    $response = [
      'items' => $profileAuditItems,
      'metadata' => [
        'total' => $profileAuditItemsCount,
      ],
    ];

    return $this->respond($response);
  }

  /**
   * Retrieves the available fields for changes.
   * Fetches all fields that have been changed in the audit records.
   * Returns the fields as a JSON response.
   *
   * @return \CodeIgniter\HTTP\Response
   */
  public function getChangesFields()
  {
    $db = \Config\Database::connect();
    $builder = $db->table('audit au');

    $builder->select('changes');
    $builder->where('au.model_name', 'People');
    $builder->where('au.changes IS NOT NULL');
    $auditItem = $builder->get()->getResultArray();

    // getting all fields from all changes
    $fields = [];
    foreach ($auditItem as $item) {
      $changes = json_decode($item['changes'], true);
      $fields = array_merge($fields, array_keys($changes['old']));
    }

    $fields = array_values(array_unique($fields));

    return $this->respond($fields);
  }

  /**
   * Processes an audit record based on the provided request data.
   * Determines the action to be taken (accept or deny) and whether a user email should be sent.
   *
   * @return \CodeIgniter\HTTP\Response
   */
  public function auditRecordProcess()
  {
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

      $dataAuditModel = new DataAuditModel();
      $result = $dataAuditModel->processAuditRecordAccept($auditId);

      if ($userEmail) {
        $dataAuditModel->sendUserEmail($auditId, $emailTemplate);
      }

      return $result;
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

    $dataAuditModel = new DataAuditModel();
    $dataAuditModel->auditRecordReviewSet($auditId, 'request_changes');

    if ($userEmail) {
      $dataAuditModel->sendUserEmail($auditId, $emailTemplate);
    }

    return 'Audit record has been updated with a changes request.';
  }
}
