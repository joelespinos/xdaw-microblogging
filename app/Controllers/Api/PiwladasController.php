<?php

namespace App\Controllers\Api;

use App\Entities\PiwladaMediaEntity;
use App\Entities\PiwladaPostEntity;
use App\Models\PiwladaMediaModel;
use App\Models\PiwladaPostModel;
use CodeIgniter\RESTful\ResourceController;
use Ramsey\Uuid\Uuid;

class PiwladasController extends ResourceController
{
    public function getComments($piwladaUuid)
    {
        $piwladaModel = new PiwladaPostModel();

        $piwladaBinaryUuid = Uuid::fromString($piwladaUuid)->getBytes();

        $comments = $piwladaModel->getCommentsByParentId($piwladaBinaryUuid)->findAll();

        $response = [
            'status'    => 200,
            'error'     => false,
            'messages'  => 'Comentaris recuperats correctament',
            'comments'  => $comments
        ];

        return $this->respondCreated($response);
    }

    public function getPiwlada($piwladaUuid)
    {
        $piwladaModel = new PiwladaPostModel();

        $piwladaBinaryUuid = Uuid::fromString($piwladaUuid)->getBytes();

        $piwlada = $piwladaModel->find($piwladaBinaryUuid);

        $response = [
            'status'    => 200,
            'error'     => false,
            'messages'  => 'Piwlada recuperada correctament',
            'piwlada'   => $piwlada
        ];

        return $this->respondCreated($response);
    }

    public function getFullPiwlada($piwladaUuid) 
    {
        $piwladaModel = new PiwladaPostModel();
        $piwladaMediaModel = new PiwladaMediaModel();

        $piwladaBinaryUuid = Uuid::fromString($piwladaUuid)->getBytes();

        $piwlada = $piwladaModel->find($piwladaBinaryUuid);

        $medias = $piwladaMediaModel->where('piwlada_uuid', $piwladaBinaryUuid)->findAll();

        $comments = $piwladaModel->getCommentsByParentId($piwladaBinaryUuid)->findAll();

        $response = [
            'status'    => 200,
            'error'     => false,
            'messages'  => 'Piwlada amb comentaris recuperada correctament',
            'piwlada'   => $piwlada,
            'medias'    => $this->appendMediaUrls($medias),
            'comments'  => $comments
        ];

        return $this->respondCreated($response);
    }

    public function getBasicInfo($piwladaUuid) {
        $piwladaModel = new PiwladaPostModel();

        $piwladaBinaryUuid = Uuid::fromString($piwladaUuid)->getBytes();

        $piwlada = $piwladaModel->getPiwladaWithUsername($piwladaBinaryUuid);

        $response = [
            'status'   => 200,
            'error'    => false,
            'messages' => 'Informació bàsica de la piwlada recuperada correctament',
            'piwlada'  => [
                'author'        => $piwlada->username,
                'author_desc'   => $piwlada->descriptive_name,
                'createdAt'     => $piwlada->created_at,
                'updatedAt'     => $piwlada->updated_at,
            ]
        ];

        return $this->respondCreated($response);
    }

    public function createPiwlada()
    {
        $validationRules = [
            'content' => 'required|min_length[1]',
        ];

        $validationMessages = [
            'content' => [
                'required'   => 'El contingut de la piwlada és obligatori.',
                'min_length' => 'El contingut de la piwlada ha de tenir almenys {param} caràcters.'
            ],
        ];

        if (!$this->validate($validationRules, $validationMessages)) {
            return $this->fail($this->validator->getErrors());
        }

        $images      = $this->request->getVar('images');
        $imagesNames = $this->request->getVar('images_names');

        $decodedImages = [];

        if ($images && !empty($images)) {
            foreach ($images as $index => $base64) {

                $imageName = $imagesNames[$index] ?? "imatge " . ($index + 1);

                if (base64_encode(base64_decode($base64, true)) !== $base64) {
                    return $this->fail("'{$imageName}' no té un format base64 vàlid");
                }

                $imageData = base64_decode($base64);
                $imageInfo = getimagesizefromstring($imageData);

                if (!$imageInfo) {
                    return $this->fail("'{$imageName}' no és una imatge vàlida");
                }

                $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!in_array($imageInfo['mime'], $allowedMimes)) {
                    return $this->fail("'{$imageName}' té un tipus no permès. Només jpeg, png, gif i webp");
                }

                $decodedImages[] = [
                    'data'         => $imageData,
                    'mime'         => $imageInfo['mime'],
                    'originalName' => $imageName,
                ];
            }
        }

        $token_data = json_decode($this->request->header("token-data")->getValue());

        $piwladaModel      = new PiwladaPostModel();
        $piwladaMediaModel = new PiwladaMediaModel();

        $newPiwlada             = new PiwladaPostEntity();
        $newPiwlada->piwlada_uuid = Uuid::uuid7();
        $newPiwlada->user_uuid    = $token_data->uuid;
        $newPiwlada->content      = $this->request->getVar('content');

        $piwladaModel->save($newPiwlada);

        $newPiwladaBinaryUuid = Uuid::fromString($newPiwlada->piwlada_uuid)->getBytes();

        if (!empty($decodedImages)) {
            foreach ($decodedImages as $image) {

                $mediaUuid = Uuid::uuid7();
                $extension = explode('/', $image['mime'])[1];
                $newName   = $mediaUuid->toString() . '.' . $extension;

                $newMedia                      = new PiwladaMediaEntity();
                $newMedia->media_uuid          = $mediaUuid;
                $newMedia->piwlada_uuid        = $newPiwlada->piwlada_uuid;
                $newMedia->file_path           = 'users-images/' . $newName;
                $newMedia->file_original_name  = $image['originalName'];
                $newMedia->mime_type           = $image['mime'];

                $piwladaMediaModel->save($newMedia);

                $destinationFolder = WRITEPATH . 'uploads/users-images';
                if (!is_dir($destinationFolder)) mkdir($destinationFolder, 0755, true);
                file_put_contents($destinationFolder . '/' . $newName, $image['data']);
            }
        }

        $addedPiwlada = $piwladaModel->find($newPiwladaBinaryUuid);
        $addedMedias  = $piwladaMediaModel->where('piwlada_uuid', $newPiwladaBinaryUuid)->findAll() ?? [];

        $response = [
            'status'   => 200,
            'error'    => false,
            'messages' => 'Piwlada creada correctament',
            'piwlada'  => $addedPiwlada,
            'medias'   => $this->appendMediaUrls($addedMedias),
        ];

        return $this->respondCreated($response);
    }

    public function commentOnPiwlada($piwladaUuid)
    {
        $validationRules = [
            'content' => 'required|min_length[1]',
        ];

        $validationMessages = [
            'content' => [
                'required'   => 'El contingut del comentari és obligatori.',
                'min_length' => 'El contingut del comentari ha de tenir almenys {param} caràcters.'
            ],
        ];

        if (!$this->validate($validationRules, $validationMessages)) {
            return $this->fail($this->validator->getErrors());
        }

        $token_data = json_decode($this->request->header("token-data")->getValue());

        $piwladaModel = new PiwladaPostModel();

        $newComment              = new PiwladaPostEntity();
        $newComment->piwlada_uuid = Uuid::uuid7();
        $newComment->parent_uuid  = $piwladaUuid;
        $newComment->user_uuid    = $token_data->uuid;
        $newComment->content      = $this->request->getVar('content');

        $piwladaModel->save($newComment);

        $addedComment = $piwladaModel->find(Uuid::fromString($newComment->piwlada_uuid)->getBytes());

        $response = [
            'status'   => 200,
            'error'    => false,
            'messages' => 'Comentari creat correctament en la piwlada ' . $piwladaUuid,
            'comment'  => $addedComment,
        ];

        return $this->respondCreated($response);
    }

    public function deletePiwlada($piwladaUuid)
    {
        $piwladaModel = new PiwladaPostModel();

        $piwladaBinaryUuid = Uuid::fromString($piwladaUuid)->getBytes();
        
        $piwladaModel->delete($piwladaBinaryUuid);                          // Esborrem la piwlada
        $piwladaModel->where('parent_uuid', $piwladaBinaryUuid)->delete();  // Esborrem els comentaris de la piwlada

        $response = [
            'status'   => 200,
            'error'    => false,
            'messages' => 'Piwlada amb id: ' . $piwladaUuid . ' s\'ha esborrat correctament',
        ];

        return $this->respondCreated($response);
    }

    public function updatePiwladaStatus($piwladaUuid)
    {
        $validationRules = [
            'status' => 'required|in_list[private,public,draft]',
        ];

        $validationMessages = [
            'status' => [
                'required' => 'L\'estat és un camp obligatori.',
                'in_list'  => 'L\'estat seleccionat no és vàlid. Ha de ser: privat, públic o esborrany.'
            ],
        ];

        if (!$this->validate($validationRules, $validationMessages)) {
            return $this->fail($this->validator->getErrors());
        }

        $piwladaModel = new PiwladaPostModel();

        $piwladaBinaryUuid = Uuid::fromString($piwladaUuid)->getBytes();

        $piwlada = $piwladaModel->find($piwladaBinaryUuid);
        $piwlada->visibility = $this->request->getVar('status');

        if ($piwlada->hasChanged()) {
            $piwladaModel->save($piwlada);
            $msg = 'L\'estat de la piwlada a canviat a ' . $this->request->getVar('status');
        } else {
            $msg = 'No hi ha canvis en l\'estat de la piwlada';
        }

        $response = [
            'status'   => 200,
            'error'    => false,
            'messages' => $msg,
            'piwlada'  => $piwlada
        ];

        return $this->respondCreated($response);
    }

    public function updatePiwlada($piwladaUuid)
    {
        $validationRules = [
            'content' => 'required|min_length[1]',
        ];

        $validationMessages = [
            'content' => [
                'required'   => 'El contingut de la piwlada és obligatori.',
                'min_length' => 'El contingut de la piwlada ha de tenir almenys {param} caràcters.'
            ],
        ];

        if (!$this->validate($validationRules, $validationMessages)) {
            return $this->fail($this->validator->getErrors());
        }

        $piwladaModel      = new PiwladaPostModel();
        $piwladaMediaModel = new PiwladaMediaModel();

        $piwladaIdBytes = Uuid::fromString($piwladaUuid)->getBytes();
        $piwlada        = $piwladaModel->find($piwladaIdBytes);

        $piwlada->content = $this->request->getVar('content');

        if ($piwlada->hasChanged()) $piwladaModel->save($piwlada);

        if ($piwlada->parent_uuid == null) {

            $oldMedias     = $this->request->getVar('old_medias') ?? [];
            $currentMedias = $piwladaMediaModel->where('piwlada_uuid', $piwladaIdBytes)->findAll();

            foreach ($currentMedias as $media) {
                if (!in_array($media->media_uuid, $oldMedias)) {
                    $piwladaMediaModel->delete(Uuid::fromString($media->media_uuid)->getBytes());

                    $fullPath = WRITEPATH . 'uploads/' . $media->file_path;
                    if (is_file($fullPath)) unlink($fullPath);
                }
            }

            $images      = $this->request->getVar('images');
            $imagesNames = $this->request->getVar('images_names');

            if ($images && !empty($images)) {
                foreach ($images as $index => $base64) {
                    $imageName = $imagesNames[$index] ?? "imatge_" . ($index + 1);

                    if (base64_encode(base64_decode($base64, true)) === $base64) {
                        $imageData = base64_decode($base64);
                        $imageInfo = getimagesizefromstring($imageData);

                        if ($imageInfo) {
                            $mediaUuid = Uuid::uuid7();
                            $extension = explode('/', $imageInfo['mime'])[1];
                            $newName   = $mediaUuid->toString() . '.' . $extension;

                            $newMedia                     = new PiwladaMediaEntity();
                            $newMedia->media_uuid         = $mediaUuid;
                            $newMedia->piwlada_uuid       = $piwladaUuid;
                            $newMedia->file_path          = 'users-images/' . $newName;
                            $newMedia->file_original_name = $imageName;
                            $newMedia->mime_type          = $imageInfo['mime'];

                            $piwladaMediaModel->save($newMedia);

                            $destinationFolder = WRITEPATH . 'uploads/users-images';
                            if (!is_dir($destinationFolder)) mkdir($destinationFolder, 0755, true);
                            file_put_contents($destinationFolder . '/' . $newName, $imageData);
                        }
                    }
                }
            }
        }

        $medias = $piwladaMediaModel->where('piwlada_uuid', $piwladaIdBytes)->findAll() ?? [];

        $response = [
            'status'   => 200,
            'error'    => false,
            'messages' => 'Piwlada actualitzada correctament',
            'piwlada'  => $piwlada,
            'medias'   => $this->appendMediaUrls($medias)
        ];

        return $this->respondCreated($response);
    }

    public function getAllPiwladas()
    {
        $piwladaModel = new PiwladaPostModel();

        $piwladas = $piwladaModel->findAll();

        $response = [
            'status'    => 200,
            'error'     => false,
            'messages'  => 'Totes les piwlades recuperades correctament',
            'piwladas'  => $piwladas
        ];

        return $this->respondCreated($response);
    }

    /**
     * Retorna un array de medias (imatges de les piwlades)
     * amb la seva url per obtenir la imatge
     * @param array $medias array de imatges de piwlades sense url
     * @return array arrayu de medias amb la url de cada media
     */
    private function appendMediaUrls(array $medias): array
    {
        foreach ($medias as $media) {
            $media->url = base_url('api/v1/media/' . $media->media_uuid);
        }

        return $medias;
    }
}
