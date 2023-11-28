<?php

namespace App\Controllers;

class Front extends BaseController
{
  public function index()
  {
    return view('front_end.html');
  }
}
