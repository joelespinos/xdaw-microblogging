<?php

namespace App\Filters\Api;

use App\Models\PiwladaPostModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\I18n\Time;
use Ramsey\Uuid\Uuid;

class PiwladaAuthorizationFilter implements FilterInterface
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
        $params      = \Config\Services::router()->params();
        $piwladaUuid = $params[0];

        $piwladaModel      = new PiwladaPostModel();
        $piwladaBinaryUuid = Uuid::fromString($piwladaUuid)->getBytes();
        $piwlada           = $piwladaModel->find($piwladaBinaryUuid);

        if (!$piwlada) {
            return responseWithRefreshedToken($request, 404, [
                'status'   => 404,
                'error'    => true,
                'messages' => 'Piwlada no trobada'
            ]);
        }

        // Obtenim dades del token
        $token_data = json_decode($request->header("token-data")->getValue());

        if ($piwlada->user_uuid != $token_data->uuid && $token_data->role != 'admin') {
            return responseWithRefreshedToken($request, 403, [
                'status'   => 403,
                'error'    => true,
                'messages' => 'No pots accedir a aquesta piwlada'
            ]);
        }

        if ($token_data->role != 'admin' && in_array('timeExpiration', $arguments ?? []) && $this->hasTimeExpired($piwlada)) {
            return responseWithRefreshedToken($request, 403, [
                'status'   => 403,
                'error'    => true,
                'messages' => 'El temps per editar aquesta piwlada ha expirat'
            ]);
        }
    }

    private function hasTimeExpired($piwlada): bool
    {
        $now         = Time::now();
        $piwladaTime = new Time($piwlada->created_at);
        $piwladaTime = $piwladaTime->addMinutes(30);
        return $now->isAfter($piwladaTime);
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