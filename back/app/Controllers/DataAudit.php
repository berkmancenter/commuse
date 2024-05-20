<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

class DataAudit extends BaseController
{
  use ResponseTrait;

  public function profileDataAudit() {
    $this->checkAdminAccess();

    $db = \Config\Database::connect();
    $builder = $db->table('audit au');
    $profileAuditItems = $builder
      ->select('
        pa.first_name AS audited_first_name,
        pa.last_name AS audited_last_name,
        pch.first_name AS changed_by_first_name,
        pch.last_name AS changed_by_last_name,
        au.id,
        au.changes,
        au.created_at
      ')
      ->join('people pa', 'pa.user_id = au.audited_id', 'left')
      ->join('people pch', 'pch.user_id = au.changed_user_id', 'left')
      ->orderBy('created_at', 'DESC')
      ->get()
      ->getResultArray();

    $profileAuditItems = array_map(function($auditItem) {
      $auditItem['changes'] = json_decode($auditItem['changes']);

      return $auditItem;
    }, $profileAuditItems);

    return $this->respond($profileAuditItems);
  }
}
