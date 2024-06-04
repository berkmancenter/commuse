<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Libraries\Cache;

/**
 * This class is responsible for handling news-related operations.
 */
class News extends BaseController
{
  use ResponseTrait;

  /**
   * Retrieves and returns the list of news items.
   *
   * @return \CodeIgniter\HTTP\ResponseInterface The response containing the list of news items.
   */
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
