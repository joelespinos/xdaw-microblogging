<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\PiwladaMediaEntity;
use Ramsey\Uuid\Uuid;

class PiwladaMediaModel extends Model
{
    protected $table            = 'piwlada_media';
    protected $primaryKey       = 'media_uuid';
    protected $useAutoIncrement = false;
    protected $returnType       = PiwladaMediaEntity::class;
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = ['piwlada_uuid', 'file_path', 'file_original_name', 'mime_type'];

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

    public function getMedias($piwlada_uuid)
    {
        $piwladaUuidBytes = Uuid::fromString($piwlada_uuid)->getBytes();
        return $this->where('piwlada_uuid', $piwladaUuidBytes)->findAll();
    }
}
