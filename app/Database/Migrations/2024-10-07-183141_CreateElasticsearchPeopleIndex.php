<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use App\Models\PeopleModel;

class CreateElasticsearchPeopleIndex extends Migration
{
  public function up()
  {
    $elasticClient = service('elasticsearchClient');
    $peopleModel = new PeopleModel();
    $indexName = $peopleModel->getSearchIndexName();

    $params = [
      'index' => $peopleModel->getSearchIndexName(),
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
            'search_content' => [
              'type' => 'text',
              'analyzer' => 'ngram_analyzer',
            ],
            'created_at' => [
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
    $peopleModel = new PeopleModel();

    $elasticClient = service('elasticsearchClient');
    $elasticClient->getClient()->indices()->delete(['index' => $peopleModel->getSearchIndexName()]);
  }
}
