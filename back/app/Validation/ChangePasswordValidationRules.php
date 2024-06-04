<?php

declare(strict_types=1);

namespace App\Validation;

use CodeIgniter\Shield\Authentication\Passwords;
use CodeIgniter\Shield\Config\Auth;

class ChangePasswordValidationRules
{
  protected Auth $config;

  /**
   * Auth Table names
   */
  protected array $tables;

  public function __construct()
  {
    /** @var Auth $authConfig */
    $authConfig = config('Auth');

    $this->config = $authConfig;
    $this->tables = $this->config->tables;
  }

  public function getRules(): array
  {
    $passwordRules = $this->getPasswordRules();
    $passwordRules['rules'][] = 'strong_password[]';

    return [
      'password' => $passwordRules,
      'password_confirm' => $this->getPasswordConfirmRules(),
    ];
  }

  public function getPasswordRules(): array
  {
    return [
      'label' => 'Auth.password',
      'rules' => ['required', Passwords::getMaxLengthRule()],
      'errors' => [
        'max_byte' => 'Auth.errorPasswordTooLongBytes',
      ],
    ];
  }

  public function getPasswordConfirmRules(): array
  {
    return [
      'label' => 'Auth.passwordConfirm',
      'rules' => 'required|matches[password]',
    ];
  }
}
