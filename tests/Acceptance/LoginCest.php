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
}
