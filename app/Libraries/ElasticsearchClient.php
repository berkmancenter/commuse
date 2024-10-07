<?php

namespace App\Libraries;

use Elasticsearch\ClientBuilder;
use Elasticsearch\Common\Exceptions\ElasticsearchException;
use Psr\Log\LoggerInterface;

/**
 * Service class for managing the Elasticsearch client.
 */
class ElasticsearchClient
{
  /**
   * @var \Elasticsearch\Client
   */
  protected $client;

  /**
   * @var LoggerInterface
   */
  protected $logger;

  /**
   * Constructor initializes the Elasticsearch client.
   */
  public function __construct()
  {
    $this->client = ClientBuilder::create()
      ->setHosts([$_ENV['elasticSearch.host']]);

    if (isset($_ENV['elasticSearch.username']) &&
        isset($_ENV['elasticSearch.password']) &&
        $_ENV['elasticSearch.username'] &&
        $_ENV['elasticSearch.password']
    ) {
      $this->client = $this->client
        ->setBasicAuthentication($_ENV['elasticSearch.username'], $_ENV['elasticSearch.password']);
    }

    $this->client = $this->client->build();

    $this->logger = service('logger');
  }

  /**
   * Method to get the instance of the Elasticsearch client.
   *
   * @return \Elasticsearch\Client
   */
  public function getClient()
  {
    return $this->client;
  }

  /**
   * Index a document in Elasticsearch.
   *
   * @param string $index  The index name.
   * @param int $id The document ID.
   * @param array $body    The document body.
   *
   * @return bool
   */
  public function indexDocument(string $index, int $id, array $body, bool $isUpdate = false): bool
  {
    $params = [
      'index' => $index,
      'id'    => $id,
      'body'  => $body,
    ];

    if ($isUpdate) {
      $params['body'] = [
        'doc'           => $body,
        'doc_as_upsert' => true,
      ];

      try {
        $this->client->update($params);
        return true;
      } catch (ElasticsearchException $e) {
        $this->logger->error("Elasticsearch update error for document ID {$id}: " . $e->getMessage());
        return false;
      }
    } else {
      try {
        $this->client->index($params);
        return true;
      } catch (ElasticsearchException $e) {
        $this->logger->error("Elasticsearch index error for document ID {$id}: " . $e->getMessage());
        return false;
      }
    }
  }

  /**
   * Delete a document from Elasticsearch.
   *
   * @param string $index The index name.
   * @param string|int $id The document ID.
   *
   * @return bool
   */
  public function deleteDocument(string $index, $id): bool
  {
    $params = [
      'index' => $index,
      'id'    => $id,
    ];

    try {
      $this->client->delete($params);
      return true;
    } catch (ElasticsearchException $e) {
      if ($e instanceof \Elasticsearch\Common\Exceptions\Missing404Exception) {
        $this->logger->error("Document not found for deletion in Elasticsearch: $id");
        return false;
      } else {
        $this->logger->error("Elasticsearch delete error for document ID {$id}: " . $e->getMessage());
        return false;
      }
    }
  }

  /**
   * Perform a search query on the specified index.
   *
   * @param string $index The name of the index to search.
   * @param array $queryBody The search query body as an associative array.
   *
   * @return array|bool Result of the search query or false if it fails.
   */
  public function search(string $index, array $queryBody)
  {
    $params = [
      'index' => $index,
      'body'  => $queryBody,
    ];

    try {
      $result = $this->client->search($params);
      $this->logger->info("Search performed on index: {$index}");
      return $result;
    } catch (ElasticsearchException $e) {
      $this->logger->error('Elasticsearch search error: ' . $e->getMessage());
      return false;
    }
  }
}
