<?php
namespace App\DataImporters;

use GuzzleHttp\Client;
use Exception;

class NewsImporter
{
  /**
   * The URL of the JSON data provider.
   *
   * @var string
   */
  protected $jsonUrl;

  /**
   * The HTTP client instance.
   *
   * @var Client
   */
  protected $client;

  /**
   * NewsImporter constructor.
   * Initializes the JSON URL and creates a new HTTP client instance.
   */
  public function __construct()
  {
    $this->jsonUrl = $_ENV['data_providers.news.url'];
    $this->client = new Client([
      'verify' => false,
    ]);
  }

  /**
   * Fetches news content items from the data provider and saves them to the database.
   *
   * @return void
   */
  public function fetchContentItems(): void
  {
    try {
      $response = $this->client->get($this->jsonUrl);
      $contentItems = json_decode($response->getBody(), true);

      $newsModel = model('NewsModel');

      foreach ($contentItems as $contentItem) {
        $existingNews = $newsModel->where('remote_url', $contentItem['link'])->first();

        if ($existingNews === null) {
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

      $cacheNewsPath = ROOTPATH . 'writable/cache/news*';
      exec("rm {$cacheNewsPath} > /dev/null 2> /dev/null");
    } catch (Exception $e) {
      log_message('error', 'Error retrieving content items from news data provider: ' . $this->jsonUrl . ' ' . $e->getMessage());
    }
  }
}
