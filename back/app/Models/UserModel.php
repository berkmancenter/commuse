<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;
use CodeIgniter\Shield\Traits\Resettable as Resettable;

class UserModel extends ShieldUserModel
{
  use Resettable;

  protected function initialize(): void
  {
    parent::initialize();
  }
}
