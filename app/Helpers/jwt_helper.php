<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * getToken
 * This function returns data from request JWT 
 * it examines request header to obtain Autorization: Bearer 
 * and decodes JWT to get payload.
 * 
 * If an error occurs on decoding, it throws a JWT Exception
 *
 * @param  mixed $cfgAPI    Config object
 * @param  mixed $request   Request object
 * @return object ['encoded' => string, "data" => object]
 */
if (!function_exists('getToken')) {

    function getToken($cfgAPI, $request)
    {
        $header = $request->header("Authorization");
        $token = null;
        if (!empty($header)) {
            if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
                $token = $matches[1];
            }
        }

        $token_data = JWT::decode($token, new Key($cfgAPI->tokenSecret, $cfgAPI->hash));

        $data = [
            "encoded" => $token,
            "data" => $token_data
        ];

        return $data;
    }
}

/**
 * renewTokenJWT
 * This function renews JWT token, with payload from a previous JWT. The
 * function renews jti (json token id), iat (issued at) and nbf (not before)
 * 
 * If an error occurs on encoding, it throws a JWT Exception
 *
 * @param  mixed $cfgAPI    Config policy object
 * @param  mixed $token_raw Token payload object
 * @return string (token)
 */
if (!function_exists('renewTokenJWT')) {

    function renewTokenJWT($cfgAPI, $token_raw)
    {
        $iat = time(); // current timestamp value

        if (isset($cfgAPI->authTimeout) && $cfgAPI->authTimeout != null)
            $token_raw->exp = $iat + $cfgAPI->authTimeout;

        $token_raw->iat = $iat;
        $token_raw->nbf = $iat;
        $token_raw->jti = \Ramsey\Uuid\Uuid::uuid7()->toString();

        $newtoken = JWT::encode((array)$token_raw, $cfgAPI->tokenSecret, $cfgAPI->hash);

        return $newtoken;
    }
}

/**
 * newTokenJWT
 * This function generates a new JWT token, it takes info from token policy
 * defined on config file. Adds payload and config items
 *
 * @param  mixed $cfgAPI    Config policy object
 * @param  mixed $data      Payload data, to add to JWT
 * @return string   Token generated
 */
if (!function_exists('newTokenJWT')) {

    function newTokenJWT($cfgAPI, $data)
    {
        $iat = time(); // current timestamp value

        $payload = array();

        if (isset($cfgAPI->authTimeout) && $cfgAPI->authTimeout != null)
            $payload["exp"] = $iat + $cfgAPI->authTimeout;

        if (isset($cfgAPI->issuer) && $cfgAPI->issuer != null)
            $payload["iss"] = $cfgAPI->issuer;

        if (isset($cfgAPI->audience) && $cfgAPI->audience != null)
            $payload["aud"] = $cfgAPI->audience;

        if (isset($cfgAPI->subject) && $cfgAPI->subject != null)
            $payload["sub"] = $cfgAPI->subject;

        $payload = array_merge(
            $payload,
            array(
                "nbf" => $iat,
                "iat" => $iat,                      // Issued at
                "jti" => \Ramsey\Uuid\Uuid::uuid7()->toString(),  // Json Token Id
            ),
            (array)$data
        );

        $token = JWT::encode($payload, $cfgAPI->tokenSecret, $cfgAPI->hash);

        return $token;
    }
}