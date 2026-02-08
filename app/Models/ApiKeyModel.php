<?php

namespace App\Models;

use CodeIgniter\Model;

class ApiKeyModel extends Model
{
    protected $table            = 'api_keys';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name',
        'api_key',
        'permission',
        'status',
        'last_used_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'name'       => 'required|max_length[100]',
        'api_key'    => 'required|max_length[64]|is_unique[api_keys.api_key,id,{id}]',
        'permission' => 'required|in_list[READ,WRITE,DELETE,ADMIN]',
        'status'     => 'permit_empty|in_list[active,disabled]',
    ];
}
