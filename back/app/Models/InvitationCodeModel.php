<?php

namespace App\Models;

use CodeIgniter\Model;

class InvitationCodeModel extends Model
{
  protected $table = 'invitation_codes';
  protected $primaryKey = 'id';
  protected $allowedFields = ['code', 'used', 'type', 'expire'];
  protected $useTimestamps = true;
  protected $dateFormat = 'datetime';
  protected $returnType = 'array';
  protected $cast = [
    'used' => 'boolean',
  ];
  protected $beforeInsert = ['generateCode'];

  protected function generateCode(array $data)
  {
    $uniqueId = uniqid();
    $hash = md5($uniqueId);
    $data['data']['code'] = $hash;

    return $data;
  }
}
