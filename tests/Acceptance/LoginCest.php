<?php

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

class LoginCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    // tests
    public function checkSiteTitleInLoginView(AcceptanceTester $I)
    {
      $I->amOnPage('/');
      $siteTitle = 'Test Portal';
      $I->see($siteTitle);
    }

    public function loginSuccessfully(AcceptanceTester $I)
    {
      $I->amOnPage('/');

      $I->createUser('loginSuccessfully@example.com', 'password123');

      $I->fillField('email', 'loginSuccessfully@example.com');
      $I->fillField('password', 'password123');
      $I->click('Login');

      $I->see('News & events');
    }

    public function loginWithInvalidCredentials(AcceptanceTester $I)
    {
      $I->amOnPage('/');

      $I->fillField('email', 'invaliduser@example.com');
      $I->fillField('password', 'invalidpassword');
      $I->click('Login');

      $I->see('Unable to log you in');
    }
}
