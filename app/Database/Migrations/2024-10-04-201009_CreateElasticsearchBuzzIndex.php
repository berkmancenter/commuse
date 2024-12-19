<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use App\Models\BuzzModel;

class CreateElasticsearchBuzzIndex extends Migration
{
  public function up()
  {
    $elasticClient = service('elasticsearchClient');
    $buzzModel = new BuzzModel();
    $indexName = $buzzModel->getSearchIndexName();

    $params = [
      'index' => $buzzModel->getSearchIndexName(),
      'body'  => [
        'settings' => [
          'analysis' => [
            'analyzer' => [
              'ngram_analyzer' => [
                'type' => 'custom',
                'tokenizer' => 'ngram_tokenizer',
                'filter' => ['lowercase'],
              ],
            ],
            'tokenizer' => [
              'ngram_tokenizer' => [
                'type' => 'nGram',
                'min_gram' => 2,
                'max_gram' => 3,
                'token_chars' => ['letter', 'digit'],
              ],
            ],
          ],
        ],
        'mappings' => [
          'properties' => [
            'id' => [
              'type' => 'integer',
            ],
            'content' => [
              'type' => 'text',
            ],
            'search_content' => [
              'type' => 'text',
              'analyzer' => 'ngram_analyzer',
            ],
            'tags' => [
              'type' => 'keyword',
            ],
            'author_name' => [
              'type' => 'text',
            ],
            'user_id' => [
              'type' => 'integer',
            ],
            'likes' => [
              'type' => 'integer',
            ],
            'person_id' => [
              'type' => 'integer',
            ],
            'image_url' => [
              'type' => 'text',
            ],
            'created_at' => [
              'type' => 'date',
              'format' => 'yyyy-MM-dd HH:mm:ss||strict_date_optional_time||epoch_millis',
            ],
            'updated_at' => [
              'type' => 'date',
              'format' => 'yyyy-MM-dd HH:mm:ss||strict_date_optional_time||epoch_millis',
            ],
          ],
        ],
      ],
    ];

    // Create the index with specified settings and mappings
    if ($elasticClient->getClient()->indices()->exists(['index' => $indexName]) === false) {
      $elasticClient->getClient()->indices()->create($params);
    }
  }

  public function down()
  {
    $buzzModel = new BuzzModel();

    $elasticClient = service('elasticsearchClient');
    $elasticClient->getClient()->indices()->delete(['index' => $buzzModel->getSearchIndexName()]);
  }
}
