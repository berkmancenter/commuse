<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomFieldModel extends Model
{
  protected $table = 'custom_fields';
  protected $primaryKey = 'id';
  protected $allowedFields = ['title', 'metadata'];
  protected $useTimestamps = true;
  protected $dateFormat = 'datetime';
  protected $returnType = 'array';
}
