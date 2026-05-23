<?php

// app/Filters/AdminFilter.php

namespace App\Filters;

use App\Services\RoleAccess;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * AdminFilter
 *
 * Gate 2 (Role) — Ensures the logged-in user has the 'admin' role.
 *
 * Allowed roles: admin ONLY
 *
 * Applied to routes:
 *  - /admin/roles          (Role Management — list)
 *  - /admin/roles/create
 *  - /admin/roles/store
 *  - /admin/roles/edit/{id}
 *  - /admin/roles/update/{id}
 *  - /admin/roles/delete/{id}
 *  - /admin/users          (User list with role assignment)
 *  - /admin/users/assign-role/{id}
 *
 * Why admin-only?
 * Role Management changes who can access what in the system.
 * It must be the most restricted resource.
 */
class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $role = session('user')['role'] ?? null;

        if (! RoleAccess::canManageUsers($role)) {
            session()->setFlashdata('error', 'Access denied. SuperAdmin privileges required.');
            return redirect()->to('/unauthorized');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
