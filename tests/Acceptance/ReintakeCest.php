<?php

namespace Tests\Acceptance;

use App\Models\PeopleModel;
use Tests\Support\AcceptanceTester;
use \App\Libraries\SystemSettingsWrapper;

class ReintakeCest
{
  private string $reintakeMessageSelector;

  public function __construct()
  {
    $this->reintakeMessageSelector = '//div[contains(text(), "' . SystemSettingsWrapper::getInstance()->getSettingByKey('ReintakeMessage')['value'] . '")]';
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
      'reintake' => PeopleModel::REINTAKE_STATUS_REQUIRED,
    ]);

    $I->fillField('email', 'reintakeMessageShowsForReintakeStatus@example.com');
    $I->fillField('password', 'password123');
    $I->click('Login');

    $I->seeElement($this->reintakeMessageSelector);
    $I->dontSeeElement('//h3[contains(text(), "News & events")]');
  }

  public function reintakeAcceptingWorks(AcceptanceTester $I)
  {
    $I->amOnPage('/');

    $I->createUser('reintakeAcceptingWorks@example.com', 'password123',
      [],
      [
      'reintake' => PeopleModel::REINTAKE_STATUS_REQUIRED,
      ],
    );

    $I->fillField('email', 'reintakeAcceptingWorks@example.com');
    $I->fillField('password', 'password123');
    $I->click('Login');

    $I->seeElement($this->reintakeMessageSelector);
    $I->dontSeeElement('//h3[contains(text(), "News & events")]');
    $I->seeElement('//a[contains(text(), "Accept")]');
    $I->click('Accept');

    $I->seeElement('//h3[contains(text(), "Edit profile")]');
    $I->dontSeeElement($this->reintakeMessageSelector);
  }

  public function reintakeDenyingWorks(AcceptanceTester $I)
  {
    $I->amOnPage('/');

    $I->createUser('reintakeDenyingWorks@example.com', 'password123',
      [],
      [
      'reintake' => PeopleModel::REINTAKE_STATUS_REQUIRED,
    ]);

    $I->fillField('email', 'reintakeDenyingWorks@example.com');
    $I->fillField('password', 'password123');
    $I->click('Login');

    $I->seeElement($this->reintakeMessageSelector);
    $I->dontSeeElement('//h3[contains(text(), "News & events")]');
    $I->seeElement('//a[contains(text(), "Decline")]');
    $I->click('Decline');

    $I->seeElement('//input[@type="email" and @name="email"]');

    $I->fillField('email', 'reintakeDenyingWorks@example.com');
    $I->fillField('password', 'password123');
    $I->click('Login');

    $I->seeElement($this->reintakeMessageSelector);
    $I->dontSeeElement('//h3[contains(text(), "News & events")]');
    $I->seeElement('//a[contains(text(), "Accept")]');
    $I->click('Accept');

    $I->seeElement('//h3[contains(text(), "Edit profile")]');
    $I->dontSeeElement($this->reintakeMessageSelector);
  }
}
