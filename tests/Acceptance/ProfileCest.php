<?php


namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

class ProfileCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    // tests
    public function saveProfile(AcceptanceTester $I)
    {
      $user = $I->createUser('saveProfile@example.com', 'password123');
      $I->loginUser($I, 'saveProfile@example.com', 'password123');

      $I->openMenu($I);
      $I->click('Edit profile');
      $I->click('Save');

      $I->seeElement('//div[contains(., "Profile has been saved.")]');

      $I->deleteUser($user->id);
    }

    public function saveProfileWithDataAndSeeProfile(AcceptanceTester $I)
    {
      $user = $I->createUser('saveProfileWithDataAndSeeProfile@example.com', 'password123');
      $I->loginUser($I, 'saveProfileWithDataAndSeeProfile@example.com', 'password123');

      $I->openMenu($I);
      $I->click('Edit profile');

      $I->checkOption('(//input[@type="checkbox"])[1]');
      $I->fillField('//label[contains(text(), "First name")]/following-sibling::div//input', 'Tommy');
      $I->fillField('//label[contains(text(), "Last name")]/following-sibling::div//input', 'Testersky');
      $I->fillField('//label[contains(text(), "Bio")]/following-sibling::div//textarea', 'You can call me Tommy, I am a tester.');

      $I->click('Save');

      $I->seeElement('//div[contains(., "Profile has been saved.")]');

      $I->click('People');

      $I->seeElement('//div[contains(., "Tommy")]');
      $I->seeElement('//div[contains(., "Testersky")]');

      $I->click('//a[contains(., "Testersky")]');

      $I->seeElement('//div[contains(., "You can call me Tommy, I am a tester.")]');

      $I->deleteUser($user->id);
    }

    public function saveProfileWithDataAndHideProfile(AcceptanceTester $I)
    {
      $user = $I->createUser('saveProfileWithDataAndHideProfile@example.com', 'password123');
      $I->loginUser($I, 'saveProfileWithDataAndHideProfile@example.com', 'password123');

      $I->openMenu($I);
      $I->click('Edit profile');

      $I->checkOption('(//input[@type="checkbox"])[1]');
      $I->fillField('//label[contains(text(), "First name")]/following-sibling::div//input', 'Tommy');
      $I->fillField('//label[contains(text(), "Last name")]/following-sibling::div//input', 'Hiddenberg');
      $I->fillField('//label[contains(text(), "Bio")]/following-sibling::div//textarea', 'You can call me Tommy, I am a tester.');

      $I->click('Save');
      $I->seeElement('//div[contains(., "Profile has been saved.")]');

      $I->click('People');

      $I->seeElement('//div[contains(., "Tommy")]');
      $I->seeElement('//div[contains(., "Hiddenberg")]');
      $I->seeElement('//div[contains(., "Found 1 user")]');

      $I->click('Edit profile');
      $I->wait(1);
      $I->uncheckOption('(//input[@type="checkbox"])[1]');
      $I->wait(1);
      $I->click('Save');
      $I->seeElement('//div[contains(., "Profile has been saved.")]');

      $I->wait(1);

      $I->click('People');

      $I->seeElement('//div[contains(., "Found 0 users")]');
      $I->dontSeeElement('//div[contains(., "Hiddenberg")]');

      $I->deleteUser($user->id);
    }
}
