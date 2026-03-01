<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class CaptchaController extends BaseController
{
    public function refresh()
    {
        $captchaLib = new \App\Libraries\Text2Image([
            'textColor'  => '#747474',
            'backColor'  => '#395786',
            'noiceLines' => 10,
            'noiceDots'  => 20,
            'imgWidth'   => 200,
            'imgHeight'  => 50
        ]);

        $captchaLib->mathCaptcha();

        session()->set('captcha_text', $captchaLib->text);

        return $this->response->setJSON([
            'status' => 'ok',
            'imatge' => $captchaLib->toImg64()
        ]);
    }
}
