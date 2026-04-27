<?php

namespace App\Controllers\Api;

use App\Models\PiwladaMediaModel;
use App\Models\PiwladaPostModel;
use CodeIgniter\RESTful\ResourceController;
use Ramsey\Uuid\Uuid;

class MediaController extends ResourceController
{
    public function getMedia(string $mediaUuid)
    {
        $mediaModel = new PiwladaMediaModel();
        $postModel  = new PiwladaPostModel();

        $mediaUuidBytes = Uuid::fromString($mediaUuid)->getBytes();
        $media = $mediaModel->find($mediaUuidBytes);

        if (!$media) {
            return $this->failNotFound('Media no trobada');
        }

        $piwladaUuidBytes = Uuid::fromString($media->piwlada_uuid)->getBytes();
        $piwlada = $postModel->find($piwladaUuidBytes);

        if (!$piwlada) {
            return $this->failNotFound('Piwlada associada no trobada');
        }

        if ($piwlada->visibility === 'private') {
            $token_data = json_decode($this->request->header('token-data')?->getValue() ?? 'null');

            if (!$token_data) {
                return $this->failUnauthorized('Aquesta media és privada');
            }

            if ($token_data->uuid !== $piwlada->user_uuid && $token_data->role !== 'admin') {
                return $this->failForbidden('No tens permís per accedir a aquesta media');
            }
        }

        $path = WRITEPATH . 'uploads/' . $media->file_path;

        if (!is_file($path)) {
            return $this->failNotFound('Fitxer no trobat al servidor');
        }

        return $this->response
            ->setHeader('Content-Type', $media->mime_type)
            ->setHeader('Content-Disposition', 'inline')
            ->setBody(file_get_contents($path));
    }
}
