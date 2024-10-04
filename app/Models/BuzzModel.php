<?php

namespace App\Models;

use CodeIgniter\Model;

class BuzzModel extends Model
{
  protected $table = 'buzz';
  protected $primaryKey = 'id';
  protected $allowedFields = ['user_id', 'content', 'parent_id', 'likes', 'tags', 'created_at', 'updated_at'];
  protected $returnType = 'array';
  protected $useTimestamps = true;

  protected $afterDelete = ['removeFromElasticsearch'];

  /**
   * Index a single buzz item to Elasticsearch.
   *
   * @param int $buzzId The ID of the buzz item
   * @param bool $isUpdate Whether this is an update operation
   * @return void
   */
  public function indexSingleBuzzItem($buzzId, $isUpdate = false)
  {
    $elasticClient = service('elasticsearchClient');

    // Retrieve the buzz item with person details
    $this->select('buzz.*, people.id as person_id, people.first_name, people.last_name, people.image_url');
    $this->join('users', 'users.id = buzz.user_id');
    $this->join('people', 'people.user_id = users.id');
    $buzzItem = $this->find($buzzId);

    if ($buzzItem) {
      $content = strip_tags($buzzItem['content']);
      $buzzItem['search_content'] = "{$buzzItem['first_name']} {$buzzItem['last_name']} " . $content;
      $buzzItem['author_name'] = "{$buzzItem['first_name']} {$buzzItem['last_name']}";
      unset($buzzItem['first_name'], $buzzItem['last_name']);

      $elasticClient->indexDocument('commuse_buzz', $buzzItem['id'], $buzzItem, $isUpdate);
    }
  }

  /**
   * Sync upserts to Elasticsearch after an insert or update.
   *
   * @param array $data
   * @return array
   */
  protected function syncToElasticsearch(array $data)
  {
    if (isset($data['id'])) {
      $this->indexSingleBuzzItem($data['id']);
    }

    return $data;
  }

  /**
   * Remove document from Elasticsearch after a delete.
   *
   * @param array $data
   * @return bool
   */
  protected function removeFromElasticsearch(array $data)
  {
    $esService = service('elasticsearchClient');

    if (isset($data['id'])) {
      $ids = is_array($data['id']) ? $data['id'] : [$data['id']];
      foreach ($ids as $id) {
        $esService->deleteDocument('commuse_buzz', $id);
      }
    }

    return true;
  }
}
