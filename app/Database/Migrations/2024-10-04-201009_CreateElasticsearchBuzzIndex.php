<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateElasticsearchBuzzIndex extends Migration
{
  public function up()
  {
    $elasticClient = service('elasticsearchClient');

    $params = [
      'index' => 'commuse_buzz',
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
    $elasticClient->getClient()->indices()->create($params);
  }

  public function down()
  {
    $elasticClient = service('elasticsearchClient');
    $elasticClient->getClient()->indices()->delete(['index' => 'commuse_buzz']);
  }
}
