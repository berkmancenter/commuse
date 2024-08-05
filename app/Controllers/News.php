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
    $paramsMd5 = md5(json_encode($this->request->getGet()));
    $cacheKey = "news_{$paramsMd5}";
    $cachedData = $cache->get($cacheKey);

    if ($cachedData && Cache::isCacheEnabled()) {
      return $this->respond($cachedData);
    }

    $newsModel = model('NewsModel');
    $paginateCurrentPage = intval($this->request->getGet('paginateCurrentPage') ?? 1);
    $limitStart = ($paginateCurrentPage - 1) * 20;

    $newsCount = $newsModel
      ->countAll();
    $news = $newsModel
      ->limit(20, $limitStart)
      ->orderBy('remote_id', 'desc')
      ->findAll();

    foreach ($news as &$newsItem) {
      $newsItem['title'] = html_entity_decode($newsItem['title']);
      $newsItem['short_description'] = html_entity_decode($newsItem['short_description']);
    }

    $response = [
      'items' => $news,
      'metadata' => [
        'total' => $newsCount,
      ],
    ];

    $cache->save($cacheKey, $response, Cache::$defaultCacheExpiration);

    return $this->respond($response);
  }
}
