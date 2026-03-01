<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\PiwladaPostEntity;
use Ramsey\Uuid\Uuid;

class PiwladaPostModel extends Model
{
    protected $table            = 'piwlada_post';
    protected $primaryKey       = 'piwlada_uuid';
    protected $useAutoIncrement = false;
    protected $returnType       = PiwladaPostEntity::class;
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = ['user_uuid', 'parent_uuid', 'content', 'visibility'];

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

    public function getPiwladasWithUserAndVisible()
    {
        $userUuid = session()->get('user_uuid');
        $userUuidBytes = Uuid::fromString($userUuid)->getBytes();
        $userRole = session()->get('user_role');

        $query = $this->select('piwlada_post.*, user_profile.username')
                    ->join('user_profile', 'piwlada_post.user_uuid = user_profile.user_uuid')
                    ->where('parent_uuid', null)
                    ->orderBy('piwlada_post.created_at', 'DESC');

        if ($userRole !== 'admin') {
            $query->groupStart()
                ->where('piwlada_post.visibility', 'public')
                ->orWhere('piwlada_post.user_uuid', $userUuidBytes)
                ->groupEnd();
        }

        return $query;
    }

    public function getPiwladaWithUsername($piwladaUuid)
    {
        return $this->select('piwlada_post.*, user_profile.username')
                    ->join('user_profile', 'piwlada_post.user_uuid = user_profile.user_uuid')
                    ->where('piwlada_post.piwlada_uuid', $piwladaUuid)
                    ->first();
    }

    public function getCommentsByParentId($piwladaUuid)
    {
        return $this->select('piwlada_post.*, user_profile.username')
                    ->join('user_profile', 'piwlada_post.user_uuid = user_profile.user_uuid')
                    ->where('piwlada_post.parent_uuid', $piwladaUuid);
    }
}
