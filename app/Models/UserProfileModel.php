<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\UserProfileEntity;
use Ramsey\Uuid\Uuid;

class UserProfileModel extends Model
{
    protected $table            = 'user_profile';
    protected $primaryKey       = 'user_uuid';
    protected $useAutoIncrement = false;
    protected $returnType       = UserProfileEntity::class;
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = ['username', 'descriptive_name', 'email', 'password_hash', 'role'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['generateUuidV7'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    protected function generateUuidV7(array $data) {

        if (!isset($data['data'][$this->primaryKey])) {

            $uuid = Uuid::uuid7();
            
            $data['data'][$this->primaryKey] = $uuid->getBytes();
        }

        return $data;
    }  
}
