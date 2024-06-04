<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * ForcePasswordChange Filter
 *
 * This filter checks if the logged-in user requires a password reset.
 * If a password reset is required, the user is redirected to the password change page.
 */
class ForcePasswordChange implements FilterInterface
{
  /**
   * Runs before the main controller method.
   *
   * @param RequestInterface $request
   * @param array|null $arguments
   * @return mixed
   */
  public function before(RequestInterface $request, $arguments = null)
  {
    $authService = service('auth');
    if ($authService->loggedIn()) {
      $user = auth()->user();
      if ($user->requiresPasswordReset()) {
        return redirect()->to(site_url('changePassword'));
      }
    }
  }

  /**
   * Runs after the main controller method.
   *
   * @param RequestInterface $request
   * @param ResponseInterface $response
   * @param array|null $arguments
   * @return void
   */
  public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
  {}
}
