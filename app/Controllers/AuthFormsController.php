<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Entities\UserProfileEntity;
use App\Models\UserProfileModel;

class AuthFormsController extends BaseController
{
    protected $helpers=['form'];

    public function loginGet()
    {
        $data['title'] = 'Inici de sessió';

        return view('auth-forms/login-form', $data);
    }

    public function loginPost()
    {
        if (!$this->isCaptchaValid(false)) {
            return redirect()->back()->withInput()->with('error', 'El codi de la imatge és incorrecte.');
        }

        $userEmail = $this->request->getPost('email');
        $userPassword = $this->request->getPost('password');

        $userProfileModel = new UserProfileModel();

        $user = $userProfileModel->where('email', $userEmail)->first();

        if ($user && $user->verifyPassword($userPassword)) {
            session()->set([
                'user_uuid'     => $user->user_uuid,
                'user_username' => $user->username,
                'user_email'    => $user->email,
                'user_role'     => $user->role,
                'loggedIn'      => true,
            ]);

            if (session()->has('urlToAcces')) return redirect()->to(base_url(session()->get('urlToAcces')));
            else return redirect()->to(base_url('/dashboard'));
        }
        
        return redirect()->to(base_url('/login'))->withInput()->with('error', 'El correu electrònic o la contrasenya són incorrectes');
        
    }

    public function registerGet()
    {
        $data['title'] = 'Registra\'t';

        return view('auth-forms/register-form', $data);
    }

    public function registerPost()
    {
        $rules = [
            'username'        => 'required|min_length[1]|max_length[100]',
            'email'           => 'required|is_unique[user_profile.email]|valid_email|max_length[150]',
            'password'        => 'required|regex_match[/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/]',
            'confirmPassword' => 'required|matches[password]'
        ];

        $validationMessages = [
            'username' => [
                'required'   => 'El nom d\'usuari és obligatori.',
                'min_length' => 'El nom d\'usuari ha de tenir almenys 1 caràcter.',
                'max_length' => 'El nom d\'usuari no pot tenir més de 100 caràcters.'
            ],
            'email' => [
                'required'    => 'El correu electrònic és obligatori.',
                'is_unique'   => 'Aquest correu electrònic ja està registrat.',
                'valid_email' => 'El correu electrònic no té un format vàlid.',
                'max_length'  => 'El correu electrònic no pot tenir més de 150 caràcters.'
            ],
            'password' => [
                'required'     => 'La contrasenya és obligatòria.',
                'regex_match'  => 'La contrasenya ha de tenir mínim 8 caràcters, una majúscula, una minúscula, un número i un caràcter especial.'
            ],
            'confirmPassword' => [
                'required' => 'Cal confirmar la contrasenya.',
                'matches'  => 'La confirmació de la contrasenya no coincideix amb la contrasenya.'
            ]
        ];

        if (!$this->validate($rules, $validationMessages) || !$this->isCaptchaValid(true)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userName = $this->request->getPost('username');
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $userModel = new UserProfileModel();
        $userToAdd = new UserProfileEntity();

        $userToAdd->username = $userName;
        $userToAdd->email = $email;
        $userToAdd->setPassword($password);

        $userModel->save($userToAdd);

        // Recuperem l'usuari insertat de la BD per a que tingui el UUID i el rol
        $userToAdd = $userModel->find($userModel->getInsertID());

        session()->set([
            'user_uuid'     => $userToAdd->user_uuid,
            'user_username' => $userToAdd->username,
            'user_email'    => $userToAdd->email,
            'user_role'     => $userToAdd->role,
            'loggedIn'      => true,
        ]);

        if (session()->has('urlToAcces')) return redirect()->to(base_url(session()->get('urlToAcces')));
        else return redirect()->to(base_url('/dashboard'));
    }

    public function logoutGet() 
    {
        session()->destroy();

        return redirect()->to(base_url('/login'))->with('msg', 'Has tancat la sessió correctament. Fins aviat!');
    }

    private function isCaptchaValid($hasListErrors)
    {
        $captchaInput = $this->request->getPost('captcha_input');

        if (! session()->has('captcha_text') || strtolower($captchaInput) !== strtolower(session()->get('captcha_text'))) {

            // $hasListErrors serveix per afegir el error de captcha als possibles errors que ja hi hagin en el validator
            if ($hasListErrors) $this->validator->setError('captcha_input', 'El codi de la imatge és incorrecte.');
            
            return false;
        }

        return true;
    }
}
