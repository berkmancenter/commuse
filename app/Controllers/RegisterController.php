<?php
namespace App\Controllers;

use CodeIgniter\Shield\Controllers\RegisterController as ShieldRegister;
use CodeIgniter\HTTP\RedirectResponse;
use App\Models\InvitationCodeModel;

class RegisterController extends ShieldRegister
{
  /**
   * Displays the registration view.
   * Retrieves the active invitation code from the 'ic' GET parameter.
   * If no valid invitation code is found, redirects to the login page with an error message.
   * Sets the invitation code in the session and calls the parent's registerView method.
   *
   * @return mixed
   */
  public function registerView()
  {
    $invitationCode = $this->getActiveInvitationCodeByCode($this->request->getGet('ic') ?? '');

    if ($invitationCode === null) {
      return redirect()->to('login')->with('errors', 'You need an invitation code to register.');
    }

    $session = \Config\Services::session();
    $session->set('invitation_code', $invitationCode['code']);

    return parent::registerView();
  }

  /**
   * Handles the registration action.
   * Retrieves the active invitation code from the session.
   * If no valid invitation code is found, redirects to the login page with an error message.
   * Sets the invitation code ID and username in the session and calls the parent's registerAction method.
   *
   * @return RedirectResponse
   */
  public function registerAction(): RedirectResponse
  {
    $session = \Config\Services::session();
    $invitationCode = $this->getActiveInvitationCodeByCode($session->get('invitation_code') ?? '');

    if ($invitationCode === null) {
      return redirect()->to('login')->with('errors', 'You need an invitation code to register.');
    }

    $session->set('invitation_code_id', $invitationCode['id']);
    $session->set('invitation_code_username', $this->request->getPost('username'));

    return parent::registerAction();
  }

  /**
   * Retrieves an active invitation code by its code value.
   * First, it searches for a single-use invitation code that matches the provided code and is unused.
   * If no single-use code is found, it searches for a multiple-use invitation code that matches the code,
   * has an expiration date greater than the current timestamp or has no expiration date set.
   *
   * @param string $code The invitation code to search for.
   * @return array|null The invitation code data if found, or null if not found.
   */
  private function getActiveInvitationCodeByCode($code)
  {
    $invitationCodeModel = new InvitationCodeModel();

    $invitationCode = $invitationCodeModel
      ->where('code', $code)
      ->where('type', 'single')
      ->where('used', false)
      ->first();

    if ($invitationCode === null) {
      $currentTimestamp = date('Y-m-d H:i:s');
      $invitationCode = $invitationCodeModel
        ->where('code', $code)
        ->where('type', 'multiple')
        ->groupStart()
        ->where('expire >', $currentTimestamp)
        ->orWhere('expire IS NULL')
        ->groupEnd()
        ->first();
    }

    return $invitationCode;
  }
}
