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
      $I->waitForElementVisible('//label[contains(text(), "First name")]/following-sibling::div//input');
      $I->click('Save');

      $I->seeElement('//div[contains(., "Profile has been saved.")]');

      $I->deleteUser($user->id);
    }

    public function saveProfileWithDataAndSeeProfile(AcceptanceTester $I)
    {
      $user = $I->createUser('saveProfileWithDataAndSeeProfile@example.com', 'password123');
      $I->loginUser($I, 'saveProfileWithDataAndSeeProfile@example.com', 'password123');

      $I->setProfileStatusActive($I);

      $I->openMenu($I);
      $I->click('Edit profile');
      $I->waitForElementVisible('//label[contains(text(), "First name")]/following-sibling::div//input');
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

      $I->setProfileStatusActive($I);

      $I->openMenu($I);
      $I->click('Edit profile');
      $I->waitForElementVisible('//label[contains(text(), "First name")]/following-sibling::div//input');
      $I->fillField('//label[contains(text(), "First name")]/following-sibling::div//input', 'Tommy');
      $I->fillField('//label[contains(text(), "Last name")]/following-sibling::div//input', 'Hiddenberg');
      $I->fillField('//label[contains(text(), "Bio")]/following-sibling::div//textarea', 'You can call me Tommy, I am a tester.');

      $I->click('Save');
      $I->seeElement('//div[contains(., "Profile has been saved.")]');

      $I->click('People');

      $I->seeElement('//div[contains(., "Tommy")]');
      $I->seeElement('//div[contains(., "Hiddenberg")]');
      $I->seeElement('//div[contains(., "Found 1 user")]');

      $I->setProfileStatusInactive($I);

      $I->click('Edit profile');
      $I->waitForElementVisible('//label[contains(text(), "First name")]/following-sibling::div//input');
      $I->click('Save');
      $I->seeElement('//div[contains(., "Profile has been saved.")]');

      $I->wait(1);

      $I->click('People');

      $I->seeElement('//div[contains(., "Found 0 users")]');
      $I->dontSeeElement('//div[contains(., "Hiddenberg")]');

      $I->deleteUser($user->id);
    }

    public function profileRedirectsOnClickableValues(AcceptanceTester $I)
    {
      $db = \Config\Database::connect();
      $builder = $db->table('custom_fields');
      $builder->where('title', 'Current country')->update(['metadata' => json_encode(['isPeopleFilter' => 'true'])]);

      $I->createUser('profileRedirectsOnClickableValues@example.com', 'password123');
      $I->loginUser($I, 'profileRedirectsOnClickableValues@example.com', 'password123');

      $I->setProfileStatusActive($I);

      $I->openMenu($I);
      $I->click('Edit profile');
      $I->waitForElementVisible('//label[contains(text(), "First name")]/following-sibling::div//input');
      $I->fillField('//label[contains(text(), "First name")]/following-sibling::div//input', 'Tommy');
      $I->fillField('//label[contains(text(), "Last name")]/following-sibling::div//input', 'Hiddenberg');
      $I->fillField('//label[contains(text(), "Current country")]/following-sibling::div//input', 'Spain');
      $I->click('Save');
      $I->seeElement('//div[contains(., "Profile has been saved.")]');

      $I->waitForJs("return document.querySelector('.awn-toast') == null", 10);
      $I->click('.top-nav-user-menu-toggler');
      $I->waitForElementVisible('//a[contains(text(), "Logout")]', 10);
      $I->click('//a[contains(text(), "Logout")]');

      $I->createUser('profileRedirectsOnClickableValues2@example.com', 'password123');
      $I->loginUser($I, 'profileRedirectsOnClickableValues2@example.com', 'password123');

      $I->setProfileStatusActive($I);

      $I->openMenu($I);
      $I->click('Edit profile');
      $I->waitForElementVisible('//label[contains(text(), "First name")]/following-sibling::div//input');
      $I->fillField('//label[contains(text(), "First name")]/following-sibling::div//input', 'Adam');
      $I->fillField('//label[contains(text(), "Last name")]/following-sibling::div//input', 'Viselberg');
      $I->fillField('//label[contains(text(), "Current country")]/following-sibling::div//input', 'Norway');
      $I->click('Save');
      $I->seeElement('//div[contains(., "Profile has been saved.")]');

      $I->click('People');

      $I->waitForElementVisible('//div[contains(text(), "Filters")]', 10);
      $I->click('Filters');
      $I->waitForElementVisible('//div[contains(text(), "Current country")]', 10);
      $I->click('//div[contains(text(), "Current country")]/following-sibling::div');
      $I->click('//span[contains(text(), "Norway")]');
      $I->click('Close');
      $I->waitForJs("return document.querySelector('.commuse-modal') == null", 10);

      $I->waitForJs("return document.evaluate('//div[contains(., \"Hiddenberg\")]', document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue === null", 10);

      $I->dontSeeElement('//div[contains(., "Hiddenberg")]');
      $I->seeElement('//div[contains(., "Viselberg")]');

      $I->click('//span[contains(text(), "Norway")]/following-sibling::img');

      $I->waitForJs("return document.evaluate('//div[contains(., \"Hiddenberg\")]', document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue !== null", 10);

      $I->seeElement('//div[contains(., "Hiddenberg")]');
      $I->seeElement('//div[contains(., "Viselberg")]');

      $I->click('Filters');
      $I->waitForElementVisible('//div[contains(text(), "Current country")]', 10);
      $I->click('//div[contains(text(), "Current country")]/following-sibling::div');
      $I->click('//span[contains(text(), "Spain")]');
      $I->click('Close');
      $I->waitForJs("return document.querySelector('.commuse-modal') == null", 10);

      $I->waitForJs("return document.evaluate('//div[contains(., \"Viselberg\")]', document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue === null", 10);

      $I->seeElement('//div[contains(., "Hiddenberg")]');
      $I->dontSeeElement('//div[contains(., "Viselberg")]');
    }
}
