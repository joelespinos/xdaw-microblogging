<?php

if (!function_exists('responseWithRefreshedToken')) {
    function responseWithRefreshedToken($request, $statusCode, $body)
    {
        // Agafem la config i les dades del token del header que ha incrustat el filtre JWT
        $token_data  = json_decode($request->header("token-data")->getValue());
        $token_config = json_decode($request->header("token-config")->getValue());

        // Generem el refreshToken igual que fa el filtre JWT
        $refreshToken = renewTokenJWT($token_config, $token_data);

        // L'incrustem en el body de la resposta
        $body['refreshToken'] = $refreshToken;

        return service('response')
            ->setStatusCode($statusCode)
            ->setJSON($body);
    }
}