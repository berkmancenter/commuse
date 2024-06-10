<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Filters;

use App\Models\UserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Shield\Authentication\Authenticators\Session;

/**
 * Force Reintake Filter.
 */
class ForceReintakeFilter implements FilterInterface
{
    /**
     * Checks if a logged in user should confirm/deny Reintake message
     *
     * @param array|null $arguments
     *
     * @return RedirectResponse|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
      if (!$request instanceof IncomingRequest) {
          return;
      }

      /** @var Session $authenticator */
      $authenticator = auth('session')->getAuthenticator();

      if ($authenticator->loggedIn()) {
        $userId = $authenticator->getUser()->id;

        $db = \Config\Database::connect();
        $builder = $db->table('people p');
        $person = $builder
          ->select('p.reintake')
          ->where('user_id', $userId)
          ->get()
          ->getResultArray();

        if (count($person)) {
          if ($person[0]['reintake'] === UserModel::REINTAKE_STATUS_REQUIRED && isset($_ENV['reintake.message'])) {
            return redirect()->to(site_url('reintake'));
          }
        }
      }
    }

    /**
     * We don't have anything to do here.
     *
     * @param array|null $arguments
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
    }
}
