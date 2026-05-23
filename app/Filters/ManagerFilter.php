<?php

namespace App\Filters;

use App\Services\RoleAccess;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ManagerFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! RoleAccess::canOperateSupplyChain(session('user')['role'] ?? null)) {
            session()->setFlashdata('error', 'Access denied. This area requires Manager or SuperAdmin access.');

            return redirect()->to('/unauthorized');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
