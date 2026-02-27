<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;
use App\Entities\Casts\UuidV7Cast;

class PiwladaPostEntity extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    
    protected $casts   = [
        'piwlada_uuid' => 'uuidv7',
        'user_uuid' => 'uuidv7',
        'parent_uuid' => 'uuidv7',
        'media_uuid' => 'uuidv7',
    ];

    protected $castHandlers = [
        'uuidv7' => UuidV7Cast::class,
    ];
}
