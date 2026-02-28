<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\PiwladaPostModel;
use Ramsey\Uuid\Uuid;

class PiwladaAuthorizationFilter implements FilterInterface
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

        $piwladaModel = new PiwladaPostModel();

        $piwladaIdBytes = Uuid::fromString($piwladaUuid)->getBytes();
        $piwladaSearch = $piwladaModel->find($piwladaIdBytes);

        // La piwlada existeix?
        if (!$piwladaSearch) {
            return redirect()->to(base_url('/dashboard'))->with('error-advice', 'Error, la piwlada no existeix!');
        }

        // L'usuari es valid per editar-la?
        if ($piwladaSearch->user_uuid != session()->get('user_uuid') && session()->get('user_role') != 'admin') {
            return redirect()->to(base_url('/dashboard'))->with('error-advice', 'Error, no pots manipular aquesta piwlada!');
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
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
