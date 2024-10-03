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
    $since = $this->request->getGet('since');

    $buzzModel = new BuzzModel();

    $query = $buzzModel->select('
        buzz.*,
        people.id as person_id,
        people.user_id,
        users.username,
        people.first_name,
        people.last_name,
        people.image_url')
      ->join('users', 'users.id = buzz.user_id')
      ->join('people', 'people.user_id = users.id')
      ->orderBy('buzz.created_at', 'asc');

    if ($since) {
      $query->where('buzz.updated_at >', $since);
    }

    $buzzItems = $query->findAll();

    foreach ($buzzItems as &$item) {
      $item['id'] = (int) $item['id'];
      $item['person_id'] = (int) $item['person_id'];
      $item['user_id'] = (int) $item['user_id'];
      $item['image_url'] = $item['image_url'] ? "profile_images/{$item['image_url']}" : '';
      $item['name'] = "{$item['first_name']} {$item['last_name']}";
      $item['tags'] = json_decode($item['tags']);
    }

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
    } else {
      $buzzModel->insert($buzzData);
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
