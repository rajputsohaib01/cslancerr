<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingModel extends Model
{
    protected $table      = 'settings';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'title', 'logo', 'facebook', 'twitter', 'instagram',
        'linkedIn', 'thread', 'whatsapp', 'pinterest',
        'created_at', 'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
