<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Entities\PiwladaMediaEntity;
use App\Entities\PiwladaPostEntity;
use App\Models\PiwladaMediaModel;
use App\Models\PiwladaPostModel;
use CodeIgniter\I18n\Time;
use Ramsey\Uuid\Uuid;
use League\CommonMark\CommonMarkConverter;

class UserPagesController extends BaseController
{

    protected $helpers=['form'];

    public function dashboardGet()
    {
        $piwladaModel = new PiwladaPostModel();
        $piwladaMediaModel = new PiwladaMediaModel();

        $piwlades = $piwladaModel->getPiwladasWithUser()->paginate(5);

        // --- CONVERSIÓ DE MARKDOWN A HTML ---
        
        // Configuració de seguretat del convertidor
        $config = [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ];

        $converter = new CommonMarkConverter($config);

        foreach ($piwlades as $piwlada) {
            $piwlada->media = $piwladaMediaModel->getMedias($piwlada->piwlada_uuid);
            $piwlada->content = $converter->convert($piwlada->content);
        }

        $data['piwlades'] = $piwlades;
        $data['pager'] = $piwladaModel->pager;
        $data['title'] = 'Menú principal';

        return view('user-pages/dashboard', $data);
    }

    public function writePiwladaGet()
    {
        $data['title'] = "Escriu una piwlada";
        return view('user-pages/piw-write', $data);
    }

    public function writePiwladaPost()
    {
        $rules = [
            'piwladaContent' => 'required|min_length[1]',
            'piwladaMedias'  => 'permit_empty|max_size[piwladaMedias,10240]|is_image[piwladaMedias]'
        ];

        $validationMessages = [
            'piwladaContent' => [
                'required'   => 'El contingut de la piwlada és obligatori.',
                'min_length' => 'El contingut de la piwlada ha de tenir almenys {param} caràcters.'
            ],
            'piwladaMedias' => [
                'max_size' => 'Els fitxers penjats no poden superar els {param}KB.',
                'is_image' => 'Només es permeten fitxers d\'imatge (jpg, png, etc.).'
            ]
        ];

        if (!$this->validate($rules, $validationMessages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $piwladaContent = $this->request->getPost('piwladaContent'); 
        $piwladaMedias = $this->request->getFileMultiple('piwladaMedias');

        $piwladaModel = new PiwladaPostModel();
        $piwladaMediaModel = new PiwladaMediaModel();

        $piwladaToAdd = new PiwladaPostEntity();
        $piwladaToAdd->user_uuid = session()->user_uuid;
        $piwladaToAdd->content = $piwladaContent;

        $piwladaModel->save($piwladaToAdd);

        // Recuperem el registre amb el ID ja inserit
        $piwladaToAdd = $piwladaModel->find($piwladaModel->getInsertID());

        if ($piwladaMedias != null) {
            foreach ($piwladaMedias as $file) {

                if ($file->isValid() && !$file->hasMoved()) {
                    $newName = $file->getRandomName();
                    
                    $piwladaMediaToAdd = new PiwladaMediaEntity();
                    $piwladaMediaToAdd->piwlada_uuid = $piwladaToAdd->piwlada_uuid;
                    $piwladaMediaToAdd->file_path = 'users-images/' . $newName;
                    $piwladaMediaToAdd->file_original_name = $file->getName();
                    $piwladaMediaToAdd->mime_type = $file->getMimeType();
                    
                    $piwladaMediaModel->save($piwladaMediaToAdd);

                    $file->move(WRITEPATH . 'uploads/users-images', $newName);
                }
            }
        }

        return redirect()->to(base_url('/dashboard'));
    }

    public function editPiwladaGet($piwladaUuid)
    {
        $piwladaModel = new PiwladaPostModel();
        $piwladaMediaModel = new PiwladaMediaModel();

        $piwladaIdBytes = Uuid::fromString($piwladaUuid)->getBytes();
        $piwladaSearch = $piwladaModel->find($piwladaIdBytes);

        if ($this->checkTimeRestriction($piwladaSearch)) 
            return redirect()->to(base_url('/dashboard'))->with('error-advice', 'Error, la piwlada a excedit els 30 minuts per ser manipulada!');

        // Recuperem totes les medias de la piwlada
        $mediasOfPiwlada = $piwladaMediaModel->where('piwlada_uuid', $piwladaIdBytes)->findAll();

        $data['piwladaMedias'] = []; // Valor per defecte de medias
        if (!empty($mediasOfPiwlada)) $data['piwladaMedias'] = $mediasOfPiwlada;

        $data['piwladaContent'] = $piwladaSearch->content;
        $data['title'] = "Edita la teva piwlada";

        return view('user-pages/piw-edit', $data);
    }

    public function editPiwladaPost($piwladaUuid)
    {
        $rules = [
            'piwladaContent' => 'required|min_length[1]',
            'piwladaMedias'  => 'permit_empty|max_size[piwladaMedias,10240]|is_image[piwladaMedias]'
        ];

        $validationMessages = [
            'piwladaContent' => [
                'required'   => 'El contingut de la piwlada és obligatori.',
                'min_length' => 'El contingut de la piwlada ha de tenir almenys {param} caràcters.'
            ],
            'piwladaMedias' => [
                'max_size' => 'Els fitxers penjats no poden superar els {param}KB.',
                'is_image' => 'Només es permeten fitxers d\'imatge (jpg, png, etc.).'
            ]
        ];

        if (!$this->validate($rules, $validationMessages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $piwladaModel = new PiwladaPostModel();
        $piwladaMediaModel = new PiwladaMediaModel();

        $piwladaIdBytes = Uuid::fromString($piwladaUuid)->getBytes();
        $piwladaSearch = $piwladaModel->find($piwladaIdBytes);

        if ($this->checkTimeRestriction($piwladaSearch)) 
            return redirect()->to(base_url('/dashboard'))->with('error-advice', 'Error, la piwlada a excedit els 30 minuts per ser manipulada!');

        if ($piwladaSearch->content != $this->request->getPost('piwladaContent')) {
            $piwladaSearch->content = $this->request->getPost('piwladaContent');
            $piwladaModel->save($piwladaSearch);
        }

        $oldMedias = $this->request->getPost('oldPiwladaMedias') ?? [];
        $allMedias = $piwladaMediaModel->where('piwlada_uuid', $piwladaIdBytes)->findAll();

        foreach ($allMedias as $media) {
            if (!in_array($media->media_uuid, $oldMedias)) {
                $mediaUuidBytes = Uuid::fromString($media->media_uuid)->getBytes();
                $piwladaMediaModel->where('media_uuid', $mediaUuidBytes)->delete();
            }
        }

        // Noves imatges
        $piwladaMedias = $this->request->getFileMultiple('piwladaMedias');

        if ($piwladaMedias != null) {
            foreach ($piwladaMedias as $file) {

                if ($file->isValid() && !$file->hasMoved()) {
                    $newName = $file->getRandomName();
                    
                    $piwladaMediaToAdd = new PiwladaMediaEntity();
                    $piwladaMediaToAdd->piwlada_uuid = $piwladaSearch->piwlada_uuid;
                    $piwladaMediaToAdd->file_path = 'users-images/' . $newName;
                    $piwladaMediaToAdd->file_original_name = $file->getName();
                    $piwladaMediaToAdd->mime_type = $file->getMimeType();
                    
                    $piwladaMediaModel->save($piwladaMediaToAdd);

                    $file->move(WRITEPATH . 'uploads/users-images', $newName);
                }
            }
        }

        return redirect()->to(base_url('/dashboard'));
        
    }

    private function checkTimeRestriction($piwladaSearch) {
        // Validació de temps posterior a la publicació
        $now = Time::now();
        $piwladaTime = new Time($piwladaSearch->created_at);
        $piwladaTime = $piwladaTime->addMinutes(30);
        return $now->isAfter($piwladaTime);
    }
}
