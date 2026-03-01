<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PiwladaMediaModel;
use App\Models\PiwladaPostModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Ramsey\Uuid\Uuid;

class ImagesMediaController extends BaseController
{
    public function getImagesFromWritable(string $mediaId)
    {
        $mediaModel = new PiwladaMediaModel();
        $postModel  = new PiwladaPostModel();

        $mediaUuidBytes = Uuid::fromString($mediaId)->getBytes();
        $media = $mediaModel->find($mediaUuidBytes);

        if (!$media) {
            throw PageNotFoundException::forPageNotFound();
        }

        $piwladaUuidBytes = Uuid::fromString($media->piwlada_uuid)->getBytes();
        $piwlada = $postModel->find($piwladaUuidBytes);

        if (!$piwlada) {
            throw PageNotFoundException::forPageNotFound();
        }

        $isLoggedIn = session()->get('loggedIn') ?? false;
        $userRole   = session()->get('user_role') ?? '';
        $userUuid   = session()->get('user_uuid') ?? '';

        if ($piwlada->visibility === 'public') {
            if (!$isLoggedIn) {
                throw PageNotFoundException::forPageNotFound();
            }
        } elseif ($piwlada->visibility === 'private') {
            if (!$isLoggedIn || ($userUuid !== $piwlada->user_uuid && $userRole !== 'admin')) {
                throw PageNotFoundException::forPageNotFound();
            }
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