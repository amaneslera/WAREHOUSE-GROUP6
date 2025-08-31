<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'users'; 
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'last_name', 'first_name', 'middle_name', 'email', 'password', 'role', 'created_at', 'updated_at'
    ];
    protected $useTimestamps = true; 

    protected $returnType     = 'array';
}