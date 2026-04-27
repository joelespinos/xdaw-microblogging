<?php

namespace App\Models;

use CodeIgniter\Model;

class TokensModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'tokens';
    protected $primaryKey       = ['tokenid', 'subject'];
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['tokenid', 'subject', 'expiration'];
    // Dates
    protected $useTimestamps = false;
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
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function get($token_data)
    {
        $data = array(
            'tokenid' => $token_data->jti,
            'subject' => $token_data->sub??'subject.not.defined'
        );
        return $this->where($data)->first();
    }

    public function revoked($token_data)
    {
        return $this->get($token_data) != null;
    }

    public function revoke($token_data)
    {
        $data = array(
            'tokenid' => $token_data->jti,
            'subject' => $token_data->sub??'subject.not.defined',
            'expiration' => $token_data->exp
        );
        return $this->insert($data);
    }
    public function purge($time=null)
    {
        if ($time===null) $time=time();
        $data = array('expiration <=' => $time);
        $query = $this->where($data)->delete();
    
        return $this->affectedRows();
    }
}
