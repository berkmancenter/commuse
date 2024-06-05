<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomFieldModel extends Model
{
  protected $table = 'custom_fields';
  protected $primaryKey = 'id';
  protected $allowedFields = ['title', 'metadata', 'parent_field_id'];
  protected $useTimestamps = true;
  protected $dateFormat = 'datetime';
  protected $returnType = 'array';
}
