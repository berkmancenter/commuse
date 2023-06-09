<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

class News extends BaseController
{
  use ResponseTrait;

  public function index()
  {
    $newsModel = model('NewsModel');

    $news = $newsModel
      ->orderBy('id', 'desc')
      ->findAll();

    return $this->respond($news);
  }
}
