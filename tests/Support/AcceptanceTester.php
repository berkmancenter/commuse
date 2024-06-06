<?php

declare(strict_types=1);

namespace Tests\Support;

require __DIR__ . DIRECTORY_SEPARATOR . '../../vendor/codeigniter4/framework/system/Test/bootstrap.php';

use CodeIgniter\Shield\Entities\User;

/**
 * Inherited Methods
 * @method void wantTo($text)
 * @method void wantToTest($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause($vars = [])
 *
 * @SuppressWarnings(PHPMD)
*/
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

    public function createUser(string $email = 'user@example.com', string $password = ''): User {
      if ($password === '') {
        $password = bin2hex(random_bytes(10));
      }
      
      $newUserData  = [
        'username' => substr(md5($email), 0, 10) . substr(md5($email), 0, 20),
        'email'    => $email,
        'password' => $password,
        'active'   => true,
      ];
      $usersProvider = auth()->getProvider();

      $user = new User($newUserData);
      $usersProvider->save($user);

      $userId = $usersProvider->getInsertID();
      $userSaved = $usersProvider->findById($userId);

      return $userSaved;
    }

    public function deleteUser($id = null) {
      if ($id === null) {
        return false;
      }

      $db = \Config\Database::connect();

      $result = $db
        ->table('users')
        ->where('id', $id)
        ->delete();

      return $result;
    }

    public function loginUser(AcceptanceTester $I, string $email = 'user@example.com', string $password = '') {
      $I->amOnPage('/login');
      $I->fillField('email', $email);
      $I->fillField('password', $password);
      $I->click('Login');
    }

    public function openMenu(AcceptanceTester $I) {
      try {
        $I->seeElement('.switmenu-menu');
        $isFound = true;
      } catch (\Exception $e) {
        $isFound = false;
      }

      if (!$isFound) {
        $I->click('.side-menu-toggler');
      }
    }
}
