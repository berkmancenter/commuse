<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\BuzzModel;
use App\Models\BuzzLikeModel;

class BuzzController extends BaseController
{
  use ResponseTrait;

  /**
   * Retrieves all buzz items with user and person details.
   *
   * @return \CodeIgniter\HTTP\Response
   */
  public function index()
  {
    // Retrieve optional search query from request
    $query = $this->request->getGet('query');
    $since = $this->request->getGet('since');

    $elasticClient = service('elasticsearchClient');

    // Construct the base query for Elasticsearch
    $searchQuery = [
      'query' => [
        'bool' => [
          'must' => [
            // Use a match_all query if no specific search query is provided
            'match_all' => new \stdClass()
          ],
        ],
      ],
      'sort' => [
        'created_at' => [ 'order' => 'asc' ],
      ],
      'size' => 100,
    ];

    // Add specific search conditions if a query is present
    if (!empty($query)) {
      $searchQuery['query']['bool']['must'] = [
        'match' => [
          'search_content' => [
            'query' => $query,
            'operator' => 'and',
          ],
        ],
      ];
    }

    // Add a filter on the update date if 'since' parameter is provided
    if ($since && $since !== 'null') {
      $searchQuery['query']['bool']['filter'][] = [
        'range' => [
          'updated_at' => [
            'gt' => $since,
          ],
        ],
      ];
    }

    // Execute the search query
    $results = $elasticClient->search('commuse_buzz', $searchQuery);

    // Check for errors in the Elasticsearch response
    if (isset($results['error'])) {
      return $this->fail($results['error']);
    }

    // Extract and format the results
    $hits = $results['hits']['hits'];
    $buzzItems = array_map(function ($hit) {
      $doc = $hit['_source'];

      return [
        'id'       => (int) $doc['id'],
        'content'  => $doc['content'],
        'tags'     => json_decode($doc['tags']),
        'person_id'  => (int) $doc['person_id'],
        'user_id'  => (int) $doc['user_id'],
        'image_url'=> $doc['image_url'] ? "profile_images/{$doc['image_url']}" : '',
        'name'     => $doc['author_name'],
        'created_at' => $doc['created_at'],
      ];
    }, $hits);

    return $this->respond($buzzItems);
  }

  /**
   * Retrieves a specific buzz item by ID with user and person details.
   *
   * @param int $id
   * @return \CodeIgniter\HTTP\Response
   */
  public function show($id)
  {
    $buzzModel = new BuzzModel();

    $buzz = $buzzModel
      ->select('buzz.*, users.username, people.first_name, people.last_name, people.image_url')
      ->join('users', 'users.id = buzz.user_id')
      ->join('people', 'people.user_id = users.id')
      ->find($id);

    if (!$buzz) {
      return $this->failNotFound('Buzz item not found');
    }

    return $this->respond($buzz);
  }

  /**
   * Creates or updates a buzz item.
   *
   * @return \CodeIgniter\HTTP\Response
   */
  public function upsert()
  {
    $buzzModel = new BuzzModel();
    $requestData = $this->request->getJSON(true);
    $requestData = array_intersect_key($requestData, array_flip(['content', 'id', 'parent_id']));
    $id = $requestData['id'] ?? null;

    if (!isset($requestData['content'])) {
      return $this->fail('Invalid input data.');
    }

    $buzzData = [
      'user_id' => auth()->id(),
      'content' => $requestData['content'],
      'parent_id' => $requestData['parent_id'] ?? null,
    ];

    // Get tags from the content
    $tags = [];
    preg_match_all('/#(\w+)/', $buzzData['content'], $tags);
    $buzzData['tags'] = json_encode($tags[1]);

    // Filter out not allowed html tags from the content
    $buzzData['content'] = strip_tags($buzzData['content'], '<a><b><i><u><strong><em><ul><ol><li><br><p>');

    // Check if the content is empty
    if (empty(trim(strip_tags($buzzData['content'])))) {
      return $this->fail('Content cannot be empty.');
    }

    if ($id) {
      $buzz = $buzzModel->find($id);
      if ((int) $buzz['user_id'] !== auth()->id()) {
        return $this->failForbidden('You are not authorized to update this item.');
      }

      $buzzModel->update($id, $buzzData);
      $buzzModel->indexSingleBuzzItem($id, true);
    } else {
      $buzzModel->insert($buzzData);
      $buzzId = $buzzModel->getInsertID();
      $buzzModel->indexSingleBuzzItem($buzzId);
    }

    return $this->respondUpdated($buzzData, 'Buzz item saved successfully.');
  }

  /**
   * Handles liking a buzz item.
   *
   * @param int $id Buzz item ID
   * @param int $userId User ID who likes the buzz item
   * @return \CodeIgniter\HTTP\Response
   */
  public function like($id)
  {
    $requestData = $this->request->getJSON(true);

    if (!isset($requestData['user_id'])) {
      return $this->fail('Invalid input data');
    }

    $userId = $requestData['user_id'];
    $buzzLikeModel = new BuzzLikeModel();

    // Check if the user has already liked the buzz item
    $existingLike = $buzzLikeModel->where('buzz_id', $id)
                                  ->where('user_id', $userId)
                                  ->first();

    if ($existingLike) {
      return $this->fail('User has already liked this item.');
    }

    $buzzLikeData = [
      'buzz_id' => $id,
      'user_id' => $userId,
    ];

    $buzzLikeModel->insert($buzzLikeData);

    // Update the likes count in the buzz table
    $buzzModel = new BuzzModel();
    $buzz = $buzzModel->find($id);
    $buzzModel->update($id, ['likes' => $buzz['likes'] + 1]);

    return $this->respond(['message' => 'Buzz item liked successfully.']);
  }

  /**
   * Deletes a buzz item.
   *
   * @param int $id
   * @return \CodeIgniter\HTTP\Response
   */
  public function delete($id)
  {
    $buzzModel = new BuzzModel();
    $buzz = $buzzModel->find($id);

    if (!$buzz) {
      return $this->failNotFound('Buzz item not found');
    }

    if ((int) $buzz['user_id'] !== auth()->id()) {
      return $this->failForbidden('You are not authorized to delete this item');
    }

    $buzzModel->delete($id);

    return $this->respondDeleted(['id' => $id], 'Buzz item deleted successfully');
  }
}
