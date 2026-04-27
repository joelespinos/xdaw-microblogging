<?php

namespace App\Filters\Api;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

use \Firebase\JWT\Key;
use \Firebase\JWT\JWT;


class JWTFilter implements FilterInterface
{

    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        helper("jwt");
        $model = new \App\Models\TokensModel();

        /**
         * L'argument 'noRenew' serveix per funcions com logout 
         * per evitar que s'incrusti el refreshToken 
         * en la resposta de la petició
         */ 
        if (isset($arguments) && $arguments[0] != 'noRenew')
            $cfgAPI = new \Config\APIJwt($arguments[0]);
        else
            $cfgAPI = new \Config\APIJwt();
      
        $header = $request->header("Authorization");
        $token = null;
        // extract the token from the header
        if (!empty($header)) {
            if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
                $token = $matches[1];
            }
        }
        // check if token is null or empty
        if (is_null($token) || empty($token)) {
            $response = service('response');
            $response->setBody('Access denied. Token required');
            $response->setStatusCode(401);
            return $response;
        } 
        try {
            $token_data = JWT::decode($token, new Key($cfgAPI->config()->tokenSecret, $cfgAPI->config()->hash));
            // check if token is defined with another policy and is not a valid token
            if (($token_data->sub ?? 'undefined') != ($cfgAPI->config()->subject ?? 'undefined')  ||
                ($token_data->aud ?? 'undefined') != ($cfgAPI->config()->audience ?? 'undefined') ||
                ($token_data->iss ?? 'undefined') != ($cfgAPI->config()->issuer ?? 'undefined')
            ) {
                $response = service('response');
                $response->setBody('Access denied. Wrong token params');
                $response->setStatusCode(401);
                return $response;
            }
            // check if token is revoked
            if ($model->revoked($token_data)) {
                $response = service('response');
                $response->setBody('Access denied. Token revoked');
                $response->setStatusCode(401);
                return $response;
            }
            // if oneTimeToken is enabled, revoke current token
            if ($cfgAPI->config()->oneTimeToken) {
                $model->revoke($token_data);
            }
            // store token data into request header to controller access
            $request->setHeader("token-data", json_encode($token_data));
            $request->setHeader("token-config", json_encode($cfgAPI->config()));
            $request->setHeader("jwt-policy", $cfgAPI->policyName);
        } catch (\Exception $ex) {
            $response = service('response');
            $response->setBody('Access denied. ' . $ex->getMessage());
            $response->setStatusCode(401);
            return $response;
        } finally {
            // clear expired tokens in revoked tokens table
            $model->purge();
        }
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return mixed
     * 
     * @link https://docs.microsoft.com/en-us/machine-learning-server/operationalize/how-to-manage-access-tokens
     */

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Obtenim el codi d'estat
        $statusCode = $response->getStatusCode();

        log_message('debug', 'After JWT Filter executat. Status: ' . $statusCode);
        log_message('debug', 'token-data header: ' . $request->header("token-data")?->getValue());

        // No renovem si és un error d'autenticació o un error de servidor
        if ($statusCode === 401 || $statusCode >= 500) {
            return $response;
        }

        helper("jwt");

        /**
         * L'argument 'noRenew' serveix per funcion com logout 
         * per evitar que s'incrusti el refreshToken 
         * en la resposta de la peticio
         */
        if (isset($arguments) && $arguments[0] != 'noRenew') {
            $cfgAPI = new \Config\APIJwt($arguments[0]);
        }
        else {
            if (isset($arguments) && $arguments[0] == 'noRenew') return;
            $cfgAPI = new \Config\APIJwt();
        }

        try {
            $values = json_decode($response->getBody());
            //check if $response->getBody() is a json string
            if (json_last_error() == JSON_ERROR_NONE) {
                if ($cfgAPI->config()->oneTimeToken && $cfgAPI->config()->autoRenew) {

                    $token_data = json_decode($request->header("token-data")->getValue());

                    $newToken = renewTokenJWT($cfgAPI->config(), $token_data);

                    $renewTokenField = $cfgAPI->config()->renewTokenField;
                    $values->$renewTokenField = $newToken;
                }
                if ($cfgAPI->config()->includePolicy)
                    $values->policy = $cfgAPI->policyName;
            }
        } catch (\Exception $ex) {
            $response = service('response');
            $response->setBody('Access denied. After. ' . $ex->getMessage());
            $response->setStatusCode(401);
        } finally {
            if ($values !== null)
                $response->setBody(json_encode($values));
        }
    }
}