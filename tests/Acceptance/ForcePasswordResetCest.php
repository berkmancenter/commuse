<?php


namespace Tests\Acceptance;

use App\Models\UserModel;
use Tests\Support\AcceptanceTester;

class ForcePasswordResetCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    // tests

    public function forcePasswordResetWorks(AcceptanceTester $I)
    {
      $I->amOnPage('/');

      $user = $I->createUser('forcePasswordResetWorks@example.com', 'password123');
      $user->forcePasswordReset();

      $I->seeElement('//input[@type="email" and @name="email"]');
      $I->fillField('email', 'forcePasswordResetWorks@example.com');
      $I->fillField('password', 'password123');
      $I->click('Login');

      $I->seeElement('//*[contains(text(), "Set your password")]');

      $I->amOnPage('/people');

      $I->seeElement('//h5[contains(text(), "Set your password")]');
      $I->dontSeeElement('//h3[contains(text(), "Edit profile")]');
      $I->fillField('password', 'Password123!');
      $I->fillField('password_confirm', 'Password123!');
      $I->click('Submit');

      $I->seeElement('//h3[contains(text(), "Edit profile")]');
      $I->dontSeeElement('//h5[contains(text(), "Set your password")]');
    }
}
