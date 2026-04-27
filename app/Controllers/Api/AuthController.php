<?php

namespace App\Controllers\Api;

use App\Models\UserProfileModel;
use CodeIgniter\RESTful\ResourceController;

class AuthController extends ResourceController
{
    protected $helpers = ['form', 'jwt'];
    
    public function login() 
    {
        $validationRules = [
            'email'     => 'required',
            'password'  => 'required'
        ];

        $validationMessages = [
            'email'         => [
                'required'  => 'Es requereix d\'un correu electrònic per iniciar sessió'
            ],
            'password'      => [
                'required'  => 'Introdueix la contrasenya per iniciar sessió'
            ],
        ];

        if (!$this->validate($validationRules, $validationMessages)) {
            return $this->fail($this->validator->getErrors());
        }

        $userProfileModel = new UserProfileModel();
        $user = $userProfileModel->where('email', $this->request->getVar('email'))->first();
        if (!$user) return $this->failNotFound('Credencials incorrectes');

        $verify = password_verify($this->request->getVar('password'), $user->password_hash);
        if (!$verify) return $this->failNotFound('Credencials incorrectes');

        /****************** GENERATE JWT TOKEN ********************/

        $APIGroupConfig = "default";
        $cfgAPI = new \Config\APIJwt($APIGroupConfig);

        $data = [
            'uuid'              => $user->user_uuid,
            'username'          => $user->username,
            'email'             => $user->email,
            'role'              => $user->role,
        ];

        $token = newTokenJWT($cfgAPI->config(), $data);

        $response = [
            'status' => 200,
            'error' => false,
            'messages' => 'Sessió iniciada correctament',
            'token' => $token
        ];

        return $this->respondCreated($response);
    }

    public function logout()
    {
        $response = [
            'status' => 200,
            'error' => false,
            'messages' => 'Sessió tancada correctament'
        ];

        return $this->respond($response);
    }
}
