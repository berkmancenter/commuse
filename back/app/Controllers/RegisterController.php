<?php

namespace App\Controllers;

use CodeIgniter\Shield\Controllers\RegisterController as ShieldRegister;
use CodeIgniter\HTTP\RedirectResponse;
use App\Models\InvitationCodeModel;

class RegisterController extends ShieldRegister
{
    public function registerView()
    {
        $invitationCode = $this->getInvitationCodeByCode($_GET['ic'] ?? '');

        if (is_null($invitationCode) === true) {
            return redirect()->to('login')->with('errors', 'You need an invitation code to register.');
        }

        $session = \Config\Services::session();
        $session->set('invitation_code', $invitationCode['code']);

        return parent::registerView();
    }

    public function registerAction(): RedirectResponse
    {
        $session = \Config\Services::session();

        $invitationCode = $this->getInvitationCodeByCode($_SESSION['invitation_code'] ?? '');

        if (is_null($invitationCode) === true) {
            return redirect()->to('login')->with('errors', 'You need an invitation code to register.');
        }

        $session->set('invitation_code_username', $_POST['username']);

        return parent::registerAction();
    }

    private function getInvitationCodeByCode($code) {
        $invitationCodeModel = new InvitationCodeModel();
        $invitationCode = $invitationCodeModel
          ->where('code', $code)
          ->where('type', 'single')
          ->where('used', false)
          ->first();

        if (is_null($invitationCode)) {
            $currentTimestamp = date('Y-m-d H:i:s', time());
            $invitationCode = $invitationCodeModel
              ->where('code', $code)
              ->where('type', 'multi')
              ->where('expire >', $currentTimestamp)
              ->first();
        }

        return $invitationCode;
    }
}
