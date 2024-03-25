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
      ->orderBy('remote_id', 'desc')
      ->findAll();

    foreach ($news as &$newsItem) {
      $newsItem['title'] = html_entity_decode($newsItem['title']);
      $newsItem['short_description'] = html_entity_decode($newsItem['short_description']);
    }

    return $this->respond($news);
  }
}
