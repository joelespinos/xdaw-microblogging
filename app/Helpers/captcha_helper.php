<?php

if (!function_exists('render_captcha')) {
    /**
     * Genera l'HTML complet d'un Captcha (Imatge + Input + Botó Refrescar + Javascript)
     * i guarda el valor inicial a la sessió.
     *
     * @param array $config Configuració opcional per a la llibreria Text2Image
     * @return string Codi HTML per imprimir a la vista
     */
    function render_captcha(array $config = []): string
    {
        // 1. Configuració per defecte (es pot sobreescriure per paràmetres)
        $defaultConfig = [
            'length'     => 5,
            'textColor'  => '#747474',
            'backColor'  => '#395786',
            'noiceLines' => 10,
            'noiceDots'  => 20,
            'imgWidth'  => 200,
            'imgHeight' => 50
        ];
        $finalConfig = array_merge($defaultConfig, $config);

        // 2. Generem el Captcha i el guardem a la sessió
        $captchaLib = new \App\Libraries\Text2Image($finalConfig);
        $captchaLib->captcha();
        session()->set('captcha_text', $captchaLib->text);

        // 3. Obtenim la imatge i la ruta per l'AJAX
        $imageBase64 = $captchaLib->toImg64();
        $refreshUrl  = base_url('captcha/refresh');

        // 4. Construïm el bloc HTML i Javascript (usant sintaxi Heredoc per claredat)
        $html = <<<HTML
            <div class="d-flex justify-content-center mb-4">
                <div class="w-100 text-white">

                    <label for="captcha_input" class="form-label mt-3">
                        <i class="fa-solid fa-shield-halved text-vivid-blue"></i>
                        Introdueix el codi de la següent imatge
                    </label>

                    <div class="d-flex align-items-center justify-content-center gap-3 mb-3 flex-wrap">
                        <img id="imatge-captcha" src="data:image/png;base64,{$imageBase64}" alt="Captcha de seguretat" class="rounded" style="border: 2px solid var(--app-border-gray);">

                        <button type="button" id="btn-refrescar-captcha" class="btn-vivid px-3 py-2 rounded">
                            <i class="fa-solid fa-rotate"></i>
                        </button>
                    </div>

                    <input type="text" name="captcha_input" id="captcha_input" class="w-100" required autocomplete="off" placeholder="Introdueix el codi aquí">

                </div>
            </div>

            <script>
            if (typeof window.captchaScriptLoaded === 'undefined') {
                window.captchaScriptLoaded = true;
                
                document.addEventListener('DOMContentLoaded', function() {
                    const btnRefrescar = document.getElementById('btn-refrescar-captcha');
                    const imgCaptcha = document.getElementById('imatge-captcha');

                    if (btnRefrescar && imgCaptcha) {
                        btnRefrescar.addEventListener('click', function() {
                            const timestamp = new Date().getTime();
                            
                            fetch('{$refreshUrl}?t=' + timestamp)
                                .then(response => response.json())
                                .then(data => {
                                    if(data.status === 'ok') {
                                        imgCaptcha.src = 'data:image/png;base64,' + data.imatge;
                                        document.getElementById('captcha_input').value = '';
                                        document.getElementById('captcha_input').focus();
                                    }
                                })
                                .catch(error => console.error('Error carregant el nou captcha:', error));
                        });
                    }
                });
            }
            </script>
            HTML;

        return $html;
    }
}