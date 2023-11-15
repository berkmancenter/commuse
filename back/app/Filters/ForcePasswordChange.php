<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ForcePasswordChange implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
      $authService = service('auth');

      if ($authService->loggedIn()) {
        $user = auth()->user();

        if ($user->requiresPasswordReset()) {
          return redirect()->to(site_url('change_password'));
        }
      }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {}
}
