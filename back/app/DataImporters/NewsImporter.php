<?php

namespace App\DataImporters;

use GuzzleHttp\Client;

class NewsImporter
{
  protected $jsonUrl;
  protected $client;

  public function __construct()
  {
    $this->jsonUrl = $_ENV['data_providers.news.url'];
    $this->client = new Client([
      'verify' => false,
    ]);
  }

  public function fetchContentItems()
  {
    try {
      $response = $this->client->get($this->jsonUrl);
      $body = $response->getBody();
      $contentItems = json_decode($body, true);

      $newsModel = model('NewsModel');

      foreach ($contentItems as $contentItem) {
        $existingNews = $newsModel
        ->where('remote_url', $contentItem['link'])
        ->first();
    
        if (is_null($existingNews) === true) {
        $newsData = [
          'title' => $contentItem['title'],
          'author' => $contentItem['author'],
          'image_url' => $contentItem['image'],
          'short_description' => $contentItem['summary'],
          'remote_id' => $contentItem['id'],
          'remote_url' => $contentItem['link'],
        ];

        $newsModel->insert($newsData);
        }
      }
    } catch (Exception $e) {
      log_message('error', 'Error retrieving content items from news data provider: ' . $this->jsonUrl . ' ' . $e->getMessage());
      return [];
    }
  }
}
