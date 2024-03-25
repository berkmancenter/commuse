<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Libraries\Cache;

class News extends BaseController
{
  use ResponseTrait;

  public function index()
  {
    $cache = \Config\Services::cache();
    $cachedData = $cache->get('news');

    if ($cachedData && Cache::isCacheEnabled()) {
      return $this->respond($cachedData);
    }

    $newsModel = model('NewsModel');

    $news = $newsModel
      ->orderBy('remote_id', 'desc')
      ->findAll();

    foreach ($news as &$newsItem) {
      $newsItem['title'] = html_entity_decode($newsItem['title']);
      $newsItem['short_description'] = html_entity_decode($newsItem['short_description']);
    }

    $cache->save('news', $news, Cache::$defaultCacheExpiration);

    return $this->respond($news);
  }
}
