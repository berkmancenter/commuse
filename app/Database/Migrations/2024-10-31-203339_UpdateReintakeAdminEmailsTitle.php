<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use App\Libraries\SystemSettingsWrapper;

class UpdateReintakeAdminEmailsTitle extends Migration
{
  public function up()
  {
    $settingsWrapper = SystemSettingsWrapper::getInstance();
    $settings = $settingsWrapper->getSettings();

    if (isset($settings['ReintakeAdminEmails'])) {
      $settings['ReintakeAdminEmails']['title'] = 'Account admin emails (reintake, expiration and account emails)';
    }

    $settingsWrapper->saveSettings($settings);
  }

  public function down()
  {
    $settingsWrapper = SystemSettingsWrapper::getInstance();
    $settings = $settingsWrapper->getSettings();

    if (isset($settings['ReintakeAdminEmails'])) {
      $settings['ReintakeAdminEmails']['title'] = 'Reintake admin emails';
    }

    $settingsWrapper->saveSettings($settings);
  }
}
