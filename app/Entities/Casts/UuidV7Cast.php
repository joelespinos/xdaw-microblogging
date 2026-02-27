<?php

namespace App\Entities\Casts;

use CodeIgniter\Entity\Cast\BaseCast;
use Ramsey\Uuid\Uuid;

class UuidV7Cast extends BaseCast
{
    /**
     * De la BD (Bytes) a l'Entitat (String)
     */
    public static function get($value, array $params = [])
    {
        if (empty($value)) {
            return null;
        }
        return Uuid::fromBytes($value)->toString();
    }

    /**
     * De l'Entitat (String/Objecte) a la BD (Bytes)
     */
    public static function set($value, array $params = [])
    {
        if (empty($value)) {
            return null;
        }

        // Si ens passen un objecte Uuid directament
        if ($value instanceof \Ramsey\Uuid\UuidInterface) {
            return $value->getBytes();
        }

        // Si és un string, el validem i convertim
        if (Uuid::isValid($value)) {
            return Uuid::fromString($value)->getBytes();
        }
        
        // Gestió d'errors bàsica (opcional)
        throw new \InvalidArgumentException("Valor no vàlid per a UUID v7: " . $value);
    }
}