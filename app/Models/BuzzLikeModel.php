<?php

namespace App\Models;

use CodeIgniter\Model;

class BuzzLikeModel extends Model
{
  protected $table = 'buzz_likes';
  protected $primaryKey = 'id';
  protected $allowedFields = ['buzz_id', 'user_id', 'created_at'];
  protected $returnType = 'array';
  protected $useTimestamps = true;
}
