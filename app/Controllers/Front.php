<?php

namespace App\Controllers;

/**
 * This class is responsible for handling the front-end requests.
 */
class Front extends BaseController
{
  /**
   * This method is the entry point for the front-end requests.
   *
   * @return string
   */
  public function index()
  {
    return view('front_end.html');
  }
}
