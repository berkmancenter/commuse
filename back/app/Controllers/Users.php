<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

class Users extends BaseController
{
  use ResponseTrait;

  public function current()
  {
    var_dump(auth()->user());
  }
}
