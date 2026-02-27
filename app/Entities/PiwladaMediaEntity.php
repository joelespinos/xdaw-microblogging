<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;
use App\Entities\Casts\UuidV7Cast;

class PiwladaMediaEntity extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    
    protected $casts   = [
        'media_uuid' => 'uuidv7',
        'piwlada_uuid' => 'uuidv7',
    ];

    protected $castHandlers = [
        'uuidv7' => UuidV7Cast::class,
    ];
}
