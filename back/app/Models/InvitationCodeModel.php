<?php

namespace App\Models;

use CodeIgniter\Model;

class InvitationCodeModel extends Model
{
  protected $table = 'invitation_codes';
  protected $primaryKey = 'id';
  protected $allowedFields = ['code', 'used'];
  protected $useTimestamps = true;
  protected $dateFormat = 'datetime';
  protected $returnType = 'array';
  protected $cast = [
    'used' => 'boolean',
  ];
}
