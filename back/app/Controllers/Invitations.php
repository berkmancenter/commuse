<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\InvitationCodeModel;

class Invitations extends BaseController
{
  use ResponseTrait;

  public function index()
  {
    $this->checkAdminAccess();

    $invitationsModel = model('InvitationCodeModel');

    $invitations = $invitationsModel
      ->orderBy('id', 'desc')
      ->findAll();

    return $this->respond($invitations);
  }

  public function upsert()
  {
    $this->checkAdminAccess();

    $result = false;
    $invitationsModel = model('InvitationCodeModel');
    $requestData = json_decode(file_get_contents('php://input'), true);
    $keysToMap = [
      'id', 'type', 'expire',
    ];
    $data = $this->mapRequestData($requestData['invitation'], $keysToMap);

    $id = $data['id'] ?? null;
    if ($data['expire'] === '') {
      $data['expire'] = null;
    }

    if ($id) {
      $existingInvitation = $invitationsModel->where('id', $id)->first();

      if ($existingInvitation) {
        $message = 'Invitation has been updated successfully.';
        $result = $invitationsModel->update($existingInvitation['id'], $data);
      }
    } else {
      unset($data['id']);
      $message = 'Invitation has been created successfully.';
      $result = $invitationsModel->insert($data);
    }

    if ($result) {
      return $this->respond(['message' => $message], 200);
    } else {
      return $this->respond(['message' => 'Error saving data.'], 500);
    }
  }

  public function delete()
  {
    $this->checkAdminAccess();

    $result = false;
    $invitationsModel = model('InvitationCodeModel');
    $requestData = json_decode(file_get_contents('php://input'), true);
    $invitationIds = $requestData['invitations'] ?? [];

    if (!empty($invitationIds)) {
      $result = $invitationsModel->whereIn('id', $invitationIds)->delete();
    }

    if ($result) {
      return $this->respond(['message' => 'Invitations have been removed successfully.'], 200);
    } else {
      return $this->respond(['message' => 'Error removing invitations.'], 500);
    }
  }
}
