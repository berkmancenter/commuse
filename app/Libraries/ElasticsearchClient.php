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
      ->setHosts([$_ENV['elasticSearch.host']])
      ->build();
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
   * @param string|int $id The document ID.
   * @param array $body    The document body.
   *
   * @return void
   */
  public function indexDocument(string $index, $id, array $body, bool $isUpdate = false)
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
        $this->logger->info("Updated document ID: {$id} in index: {$index}");
      } catch (ElasticsearchException $e) {
        $this->logger->error("Elasticsearch update error for document ID {$id}: " . $e->getMessage());
      }
    } else {
      try {
        $this->client->index($params);
        $this->logger->info("Indexed document ID: {$id} in index: {$index}");
      } catch (ElasticsearchException $e) {
        $this->logger->error("Elasticsearch index error for document ID {$id}: " . $e->getMessage());
      }
    }
  }

  /**
   * Delete a document from Elasticsearch.
   *
   * @param string $index The index name.
   * @param string|int $id The document ID.
   *
   * @return void
   */
  public function deleteDocument(string $index, $id)
  {
    $params = [
      'index' => $index,
      'id'    => $id,
    ];

    try {
      $this->client->delete($params);
      $this->logger->info("Deleted document ID: {$id} from index: {$index}");
    } catch (ElasticsearchException $e) {
      if ($e instanceof \Elasticsearch\Common\Exceptions\Missing404Exception) {
        $this->logger->info("Document not found for deletion in Elasticsearch: $id");
      } else {
        $this->logger->error("Elasticsearch delete error for document ID {$id}: " . $e->getMessage());
      }
    }
  }

  /**
   * Perform a search query on the specified index.
   *
   * @param string $index The name of the index to search.
   * @param array $queryBody The search query body as an associative array.
   *
   * @return array Result of the search query.
   */
  public function search(string $index, array $queryBody): array
  {
    $params = [
      'index' => $index,
      'body'  => $queryBody,
    ];

    try {
      return $this->client->search($params);
    } catch (ElasticsearchException $e) {
      $this->logger->error('Elasticsearch search error: ' . $e->getMessage());
      return ['error' => $e->getMessage()];
    }
  }
}
