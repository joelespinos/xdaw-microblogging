<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Entities\PiwladaMediaEntity;
use App\Entities\PiwladaPostEntity;
use App\Models\PiwladaMediaModel;
use App\Models\PiwladaPostModel;
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
            'piwladaContent' => 'required|min_length[5]',
            'piwladaMedias'  => 'max_size[piwladaMedias,10240]|is_image[piwladaMedias]'
        ];

        $validationMessages = [
            'piwladaContent' => [
                'required'   => 'El contingut de la piwlada és obligatori.',
                'min_length' => 'El contingut de la piwlada ha de tenir almenys 5 caràcters.'
            ],
            'piwladaMedias' => [
                'max_size' => 'Els fitxers penjats no poden superar els 10MB.',
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

        $piwladaToAdd = $piwladaModel->find($piwladaModel->getInsertID());

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

        return redirect()->to(base_url('/dashboard'));
    }

    public function editPiwladaGet($piwladaUuid)
    {
        $piwladaModel = new PiwladaPostModel();
        $piwladaMediaModel = new PiwladaMediaModel();

        $piwladaIdBytes = Uuid::fromString($piwladaUuid)->getBytes();
        $piwladaSearch = $piwladaModel->find($piwladaIdBytes);

        // La piwlada existeix?
        if (!$piwladaSearch) {
            return redirect()->to(base_url('/dashboard'))->with('error-advice', 'Error, la piwlada a editar no existeix!');

        // L'usuari es valid per editar-la?
        } else if ($piwladaSearch->user_uuid != session()->get('user_uuid') && session()->get('user_role') != 'admin') {
            return redirect()->to(base_url('/dashboard'))->with('error-advice', 'Error, no pots editar aquesta piwlada!');
        }

        // Recuperem totes les medias de la piwlada
        $mediasOfPiwlada = $piwladaMediaModel->where('piwlada_uuid', $piwladaIdBytes)->findAll();

        $data['piwladaMedias'] = []; // Valor per defecte de medias
        if (!empty($mediasOfPiwlada)) $data['piwladaMedias'] = $mediasOfPiwlada;

        $data['piwladaContent'] = $piwladaSearch->content;
        $data['title'] = "Edita la teva piwlada";

        return view('user-pages/piw-edit', $data);
    }
}
