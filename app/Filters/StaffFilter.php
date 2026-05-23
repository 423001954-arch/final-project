<?php

namespace App\Filters;

use App\Services\RoleAccess;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class StaffFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! RoleAccess::canRequestStock(session('user')['role'] ?? null)) {
            session()->setFlashdata('error', 'Access denied. Staff access required.');

            return redirect()->to('/unauthorized');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
