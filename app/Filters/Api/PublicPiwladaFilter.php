<?php

namespace App\Filters\Api;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\PiwladaPostModel;
use Ramsey\Uuid\Uuid;

class PublicPiwladaFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper(['jwt', 'filter']);

        // Comprovem si la ruta és privada (té JWT) i necessita refreshToken
        $withToken = isset($arguments) && in_array('withToken', $arguments);

        $params      = \Config\Services::router()->params();
        $piwladaUuid = $params[0];

        $piwladaModel      = new PiwladaPostModel();
        $piwladaBinaryUuid = Uuid::fromString($piwladaUuid)->getBytes();
        $piwlada           = $piwladaModel->find($piwladaBinaryUuid);

        if (!$piwlada) {
            return $this->buildResponse($request, 404, [
                'status'   => 404,
                'error'    => true,
                'messages' => 'Piwlada no trobada'
            ], $withToken);
        }

        if ($piwlada->visibility != 'public') {
            return $this->buildResponse($request, 403, [
                'status'   => 403,
                'error'    => true,
                'messages' => 'No pots accedir a aquesta piwlada'
            ], $withToken);
        }
    }

    /**
     * Com aquesta filtre es pot utilitzar tant en rutes publiques com en privades
     * amb l'argument :withToken controlem si el filtre se esta aplicant sobre una
     * ruta que necessita token (es a dir una ruta privada).
     * Segons si l'argument hi es o no, construim la resposta amb el refreshToken o no
     */
    private function buildResponse($request, $statusCode, $body, $withToken)
    {
        if ($withToken) {
            return responseWithRefreshedToken($request, $statusCode, $body);
        }

        return service('response')
            ->setStatusCode($statusCode)
            ->setJSON($body);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}