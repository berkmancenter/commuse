<?php


namespace Tests\Acceptance;

use App\Models\UserModel;
use Tests\Support\AcceptanceTester;
use \App\Libraries\SystemSettingsWrapper;

class ReintakeCest
{
    public function _before(AcceptanceTester $I)
    {
      $settings = SystemSettingsWrapper::getInstance()->getSettings();
      $settings['ReintakeMessage'] = ['value' => '<p>Reintake message</p>'];
      SystemSettingsWrapper::getInstance()->saveSettings($settings);
    }

    // tests

    public function reintakeMessageDoesntShowForDefaultStatus(AcceptanceTester $I)
    {
      $I->amOnPage('/');

      $I->createUser('reintakeMessageDoesntShowForDefaultStatus@example.com', 'password123');

      $I->fillField('email', 'reintakeMessageDoesntShowForDefaultStatus@example.com');
      $I->fillField('password', 'password123');
      $I->click('Login');

      $I->seeElement('//h3[contains(text(), "News & events")]');
      $I->dontSeeElement('//input[@type="email" and @name="email"]');
    }

    public function reintakeMessageShowsForReintakeStatus(AcceptanceTester $I)
    {
      $I->amOnPage('/');

      $I->createUser('reintakeMessageShowsForReintakeStatus@example.com', 'password123',
        [],
        [
        'reintake' => UserModel::REINTAKE_STATUS_REQUIRED,
      ]);

      $I->fillField('email', 'reintakeMessageShowsForReintakeStatus@example.com');
      $I->fillField('password', 'password123');
      $I->click('Login');

      $I->seeElement('//p[contains(text(), "Reintake message")]');
      $I->dontSeeElement('//h3[contains(text(), "News & events")]');
    }

    public function reintakeAcceptingWorks(AcceptanceTester $I)
    {
      $I->amOnPage('/');

      $I->createUser('reintakeAcceptingWorks@example.com', 'password123',
        [],
        [
        'reintake' => UserModel::REINTAKE_STATUS_REQUIRED,
      ]);

      $I->fillField('email', 'reintakeAcceptingWorks@example.com');
      $I->fillField('password', 'password123');
      $I->click('Login');

      var_dump(\App\Libraries\SystemSettingsWrapper::getInstance()->getSettingByKey('ReintakeMessage')['value']);

      $I->seeElement('//p[contains(text(), "Reintake message")]');
      $I->dontSeeElement('//h3[contains(text(), "News & events")]');
      $I->seeElement('//a[contains(text(), "Accept")]');
      $I->click('Accept');

      $I->seeElement('//h3[contains(text(), "Edit profile")]');
      $I->dontSeeElement('//p[contains(text(), "Reintake message")]');
    }

    public function reintakeDenyingWorks(AcceptanceTester $I)
    {
      $I->amOnPage('/');

      $I->createUser('reintakeDenyingWorks@example.com', 'password123',
        [],
        [
        'reintake' => UserModel::REINTAKE_STATUS_REQUIRED,
      ]);

      $I->fillField('email', 'reintakeDenyingWorks@example.com');
      $I->fillField('password', 'password123');
      $I->click('Login');

      $I->seeElement('//p[contains(text(), "Reintake message")]');
      $I->dontSeeElement('//h3[contains(text(), "News & events")]');
      $I->seeElement('//a[contains(text(), "Decline")]');
      $I->click('Decline');

      $I->seeElement('//input[@type="email" and @name="email"]');

      $I->fillField('email', 'reintakeDenyingWorks@example.com');
      $I->fillField('password', 'password123');
      $I->click('Login');

      $I->seeElement('//h3[contains(text(), "News & events")]');
      $I->dontSeeElement('//p[contains(text(), "Reintake message")]');
    }
}
