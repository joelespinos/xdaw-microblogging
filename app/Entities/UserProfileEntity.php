<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;
use App\Entities\Casts\UuidV7Cast;

class UserProfileEntity extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];

    protected $casts   = [
        'user_uuid' => 'uuidv7',
    ];

    protected $castHandlers = [
        'uuidv7' => UuidV7Cast::class,
    ];

    public function setPassword(string $password) {
        $this->attributes['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        return $this;
    }

    public function verifyPassword(string $inputPassword): bool {
        return password_verify($inputPassword, $this->attributes['password_hash']);
    }
}
