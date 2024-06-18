<?php


namespace Tests\Acceptance;

use App\Models\InvitationCodeModel;
use Tests\Support\AcceptanceTester;

class InvitationsCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    // tests

    public function correctInviatationCodeAllowsRegistration(AcceptanceTester $I)
    {
      $invitationsModel = new InvitationCodeModel();
      $invitationsModel->insert([
        'type' => 'single',
      ]);
      $invitation = $invitationsModel
        ->select()
        ->first();

      $I->amOnPage('/register?ic=' . $invitation['code']);

      $I->seeElement('//input[@type="email" and @name="email"]');
      $I->fillField('email', 'correctInviatationCodeAllowsRegistration@example.com');
      $I->fillField('username', 'correctInviatationCodeAllowsR');
      $I->fillField('password', 'password123');
      $I->fillField('password_confirm', 'password123');
      $I->click('Register');

      $I->seeElement('//h3[contains(text(), "Edit profile")]');
    }

    public function wrongInviatationCodeDoesntAllowRegistration(AcceptanceTester $I)
    {
      $invitationsModel = new InvitationCodeModel();
      $invitationsModel->insert([
        'type' => 'single',
      ]);
      $invitationsModel
        ->select()
        ->orderBy('id', 'DESC')
        ->first();

      $I->amOnPage('/register?ic=imsowronggeez');

      $I->seeElement('//div[contains(., "The invitation code is not valid.")]');
    }

    public function singleInviatationCodeWontAllowToRegisterTwice(AcceptanceTester $I)
    {
      $invitationsModel = new InvitationCodeModel();
      $invitationsModel->insert([
        'type' => 'single',
      ]);
      $invitation = $invitationsModel
        ->select()
        ->orderBy('id', 'DESC')
        ->first();

      $I->amOnPage('/register?ic=' . $invitation['code']);

      $I->seeElement('//input[@type="email" and @name="email"]');
      $I->fillField('email', 'singleInviatationCodeWontAllowToRegisterTwice@example.com');
      $I->fillField('username', 'singleInviatationCodeWontAll');
      $I->fillField('password', 'password123');
      $I->fillField('password_confirm', 'password123');
      $I->click('Register');

      $I->seeElement('//h3[contains(text(), "Edit profile")]');

      $I->click('.top-nav-user-menu-toggler');
      $I->waitForElementVisible('//a[contains(text(), "Logout")]', 10);
      $I->click('//a[contains(text(), "Logout")]');

      $I->amOnPage('/register?ic=' . $invitation['code']);

      $I->seeElement('//div[contains(., "The invitation code is not valid.")]');
    }

    public function multipleInviatationCodeAllowsToRegisterTwice(AcceptanceTester $I)
    {
      $invitationsModel = new InvitationCodeModel();
      $invitationsModel->insert([
        'type' => 'multiple',
      ]);
      $invitation = $invitationsModel
        ->select()
        ->orderBy('id', 'DESC')
        ->first();

      $I->amOnPage('/register?ic=' . $invitation['code']);

      $I->seeElement('//input[@type="email" and @name="email"]');
      $I->fillField('email', 'multipleInviatationCodeAllowsToRegisterTwice1@example.com');
      $I->fillField('username', 'multipleInviatationCodeAllows1');
      $I->fillField('password', 'password123');
      $I->fillField('password_confirm', 'password123');
      $I->click('Register');

      $I->seeElement('//h3[contains(text(), "Edit profile")]');

      $I->click('.top-nav-user-menu-toggler');
      $I->waitForElementVisible('//a[contains(text(), "Logout")]', 10);
      $I->click('//a[contains(text(), "Logout")]');

      $I->amOnPage('/register?ic=' . $invitation['code']);

      $I->seeElement('//input[@type="email" and @name="email"]');
      $I->fillField('email', 'multipleInviatationCodeAllowsToRegisterTwice2@example.com');
      $I->fillField('username', 'multipleInviatationCodeAllows2');
      $I->fillField('password', 'password123');
      $I->fillField('password_confirm', 'password123');
      $I->click('Register');

      $I->seeElement('//h3[contains(text(), "Edit profile")]');
    }

    public function timeLimitedInviatationCodeAllowsRegistration(AcceptanceTester $I)
    {
      // Multiple type
      $this->timeLimitedInviatationCodeAllowsRegistrationTest($I, 'multiple');

      // Single type
      $this->timeLimitedInviatationCodeAllowsRegistrationTest($I, 'single');
    }

    public function expiredTimeLimitedInviatationCodeAllowsRegistration(AcceptanceTester $I)
    {
      // Multiple type
      $this->expiredTimeLimitedInviatationCodeDoesntAllowRegistrationTest($I, 'multiple');

      // Single type
      $this->expiredTimeLimitedInviatationCodeDoesntAllowRegistrationTest($I, 'single');
    }

    private function timeLimitedInviatationCodeAllowsRegistrationTest(AcceptanceTester $I, $type)
    {
      $invitationsModel = new InvitationCodeModel();
      $invitationsModel->insert([
        'type' => $type,
        'expire' => date('Y-m-d H:i:s', time() + 120),
      ]);
      $invitation = $invitationsModel
        ->select()
        ->orderBy('id', 'DESC')
        ->first();

      $I->amOnPage('/register?ic=' . $invitation['code']);

      $I->seeElement('//input[@type="email" and @name="email"]');
      $I->fillField('email', 'timeLimitedInviatationCodeAllowsRegistration' . $type . '@example.com');
      $I->fillField('username', 'timeLimitedInvCoAl' . $type);
      $I->fillField('password', 'password123');
      $I->fillField('password_confirm', 'password123');
      $I->click('Register');

      $I->seeElement('//h3[contains(text(), "Edit profile")]');

      $I->click('.top-nav-user-menu-toggler');
      $I->waitForElementVisible('//a[contains(text(), "Logout")]', 10);
      $I->click('//a[contains(text(), "Logout")]');
    }

    private function expiredTimeLimitedInviatationCodeDoesntAllowRegistrationTest(AcceptanceTester $I, $type)
    {
      $invitationsModel = new InvitationCodeModel();
      $invitationsModel->insert([
        'type' => $type,
        'expire' => date('Y-m-d H:i:s', time() - 120),
      ]);
      $invitation = $invitationsModel
        ->select()
        ->orderBy('id', 'DESC')
        ->first();

      $I->amOnPage('/register?ic=' . $invitation['code']);

      $I->seeElement('//div[contains(., "The invitation code is not valid.")]');
    }
}
