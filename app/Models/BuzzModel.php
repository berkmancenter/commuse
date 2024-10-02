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
}
