<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PiwladaMediaModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Ramsey\Uuid\Uuid;

class ImagesMediaController extends BaseController
{
    public function getImagesFromWritable(string $mediaId)
    {
        $mediaModel = new PiwladaMediaModel();

        // Convertim el UUID de string a bytes
        $mediaUuidBytes = Uuid::fromString($mediaId)->getBytes();
        
        // Busquem el media per el UUID en bytes
        $media = $mediaModel->find($mediaUuidBytes);

        if (!$media) {
            throw PageNotFoundException::forPageNotFound();
        }

        $path = WRITEPATH . 'uploads/' . $media->file_path;

        if (!is_file($path)) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->response
            ->setHeader('Content-Type', $media->mime_type)
            ->setHeader('Content-Disposition', 'inline')
            ->setBody(file_get_contents($path));
    }
}