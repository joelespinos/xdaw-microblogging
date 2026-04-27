<?php

namespace App\Controllers\Api;

use App\Models\UserProfileModel;
use CodeIgniter\RESTful\ResourceController;
use Ramsey\Uuid\Uuid;

class UserProfilesController extends ResourceController
{
    public function getUser($userUuid)
    {
        $userProfileModel = new UserProfileModel();

        $userBinaryUuid = Uuid::fromString($userUuid)->getBytes();

        $user = $userProfileModel->find($userBinaryUuid);
        if (!$user) return $this->failNotFound('Usuari no trobat');

        $response = [
            'status'    => 200,
            'error'     => false,
            'messages'  => 'Usuari recuperat correctament',
            'user'      => [
                'user_id'          => $user->user_uuid,
                'username'         => $user->username,
                'descriptive_name' => $user->descriptive_name,
                'email'            => $user->email,
                'role'             => $user->role,
            ]
        ];

        return $this->respondCreated($response);
    }

    public function updateUser($userUuid)
    {
        $validationRules = [
            'username'         => 'required|min_length[1]|max_length[100]',
            'descriptive_name' => 'required|min_length[1]|max_length[100]',
            'email'            => 'required|valid_email|max_length[150]',
            'password'         => 'required|regex_match[/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/]',
        ];

        $validationMessages = [
            'username' => [
                'required'   => 'El nom d\'usuari és obligatori.',
                'min_length' => 'El nom d\'usuari ha de tenir almenys 1 caràcter.',
                'max_length' => 'El nom d\'usuari no pot tenir més de 100 caràcters.'
            ],
            'descriptive_name' => [
                'required'   => 'El nom descriptiu d\'usuari és obligatori.',
                'min_length' => 'El nom descriptiu d\'usuari ha de tenir almenys 1 caràcter.',
                'max_length' => 'El nom descriptiu d\'usuari no pot tenir més de 100 caràcters.'
            ],
            'email' => [
                'required'    => 'El correu electrònic és obligatori.',
                'valid_email' => 'El correu electrònic no té un format vàlid.',
                'max_length'  => 'El correu electrònic no pot tenir més de 150 caràcters.'
            ],
            'password' => [
                'required'    => 'La contrasenya és obligatòria.',
                'regex_match' => 'La contrasenya ha de tenir mínim 8 caràcters, una majúscula, una minúscula, un número i un caràcter especial.'
            ],
        ];

        $token_data = json_decode($this->request->header("token-data")->getValue());

        if ($token_data->role == 'admin') {
            $validationRules['role'] = 'required|in_list[standard,admin]';
            $validationMessages['role'] = [
                'required' => 'El camp rol és obligatori',
                'in_list'  => 'El rol ha de ser standard o admin'
            ];
        }

        if (!$this->validate($validationRules, $validationMessages)) {
            return $this->fail($this->validator->getErrors());
        }

        $userProfileModel = new UserProfileModel();

        $userBinaryUuid = Uuid::fromString($userUuid)->getBytes();
        $user           = $userProfileModel->find($userBinaryUuid);

        $userWithEmail = $userProfileModel->where('email', $this->request->getVar('email'))->first();
        if ($userWithEmail && $userWithEmail->user_uuid != $user->user_uuid) {
            return $this->fail('Aquest correu ja esta enregistrat per un altre usuari');
        }

        $user->username         = $this->request->getVar('username');
        $user->descriptive_name = $this->request->getVar('descriptive_name');
        $user->email            = $this->request->getVar('email');
        $user->setPassword($this->request->getVar('password'));
        if ($token_data->role == 'admin') $user->role = $this->request->getVar('role');

        if ($user->hasChanged()) {
            $userProfileModel->save($user);
            $msg = "Usuari actualizat correctament";
        } else {
            $msg = "No hi ha canvis per realitzar";
        }

        $response = [
            'status'   => 200,
            'error'    => false,
            'messages' => $msg,
            'user'     => [
                'user_id'          => $user->user_uuid,
                'username'         => $user->username,
                'descriptive_name' => $user->descriptive_name,
                'email'            => $user->email,
                'role'             => $user->role,
            ]
        ];

        return $this->respondCreated($response);
    }

    public function getAllUsers()
    {
        $userProfileModel = new UserProfileModel();

        $users = $userProfileModel->findAll();

        $response = [
            'status'   => 200,
            'error'    => false,
            'messages' => 'Usuaris recuperats correctament',
            'users'    => $users,
        ];

        return $this->respondCreated($response);
    }
}
