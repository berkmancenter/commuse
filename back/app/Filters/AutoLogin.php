<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AutoLogin implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
      if ($_ENV['CI_ENVIRONMENT'] !== 'production' && isset($_ENV['auth.autoLogin']) && !auth()->loggedIn()) {
        auth()->attempt([
            'email' => 'dev@example.com',
            'password' => 'dev',
        ]);
      }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {}
}
