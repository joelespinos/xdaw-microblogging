<?php

namespace App\Filters\Api;

use App\Models\UserProfileModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Ramsey\Uuid\Uuid;

class UserAuthorizationFilter implements FilterInterface
{
    /**
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        helper(['jwt', 'filter']);

        // Obtenim el primer parametre de ruta, p.ex: ruta/(:uuid) <- aquest es el parametre 0
        $params = \Config\Services::router()->params();
        $userUuid = $params[0];

        $userProfileModel = new UserProfileModel();
        $userBinaryUuid   = Uuid::fromString($userUuid)->getBytes();
        $user             = $userProfileModel->find($userBinaryUuid);

        if (!$user) {
            return responseWithRefreshedToken($request, 404, [
                'status'   => 404,
                'error'    => true,
                'messages' => 'Usuari no trobat'
            ]);
        }

        // Obtenim dades del token
        $token_data = json_decode($request->header("token-data")->getValue());

        if ($user->user_uuid != $token_data->uuid && $token_data->role != 'admin') {
            return responseWithRefreshedToken($request, 403, [
                'status'   => 403,
                'error'    => true,
                'messages' => 'No pots accedir a aquest usuari'
            ]);
        }

    }

    /**
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
