<?php

namespace App\Filters;

use App\Models\PiwladaPostModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Ramsey\Uuid\Uuid;

class PiwladaCommentsAccessFilter implements FilterInterface
{
    /**
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Utilitzem el service router per a obtenir els parametres de ruta -> per exemple: ruta/(:uuid) <- aquest es el parametre 0
        $params = \Config\Services::router()->params();
        $piwladaUuid = $params[0];

        $piwladaPostModel = new PiwladaPostModel();

        $piwladaUuidBytes = Uuid::fromString($piwladaUuid)->getBytes();
        $piwladaSearch = $piwladaPostModel->find($piwladaUuidBytes);

        // La piwlada existeix?
        if (!$piwladaSearch) {
            return redirect()->to(base_url('/dashboard'))->with('error-advice', 'Error, la piwlada no existeix!');

        // La piwlada es privada?
        } else if ($piwladaSearch->visibility == 'private' && $piwladaSearch->user_uuid != session()->get('user_uuid') && session()->get('user_role') != 'admin') {
            return redirect()->to(base_url('/dashboard'))->with('error-advice', 'Error, no pots accedir a aquesta piwlada!');
        }
    }

    /**
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
