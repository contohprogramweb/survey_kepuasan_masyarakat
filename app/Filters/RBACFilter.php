<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

/**
 * RBAC Filter - Middleware untuk Role-Based Access Control
 * Mengecek apakah user memiliki role/permission yang diperlukan
 */
class RBACFilter implements FilterInterface
{
    /**
     * Cek authorization berdasarkan role atau permission
     *
     * @param RequestInterface $request
     * @param array|null       $arguments Format: ['role:admin'] atau ['permission:survey.create']
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (empty($arguments)) {
            return;
        }

        $session = service('session');
        $userRoles = $session->get('roles') ?? [];
        $userPermissions = $session->get('permissions') ?? [];

        foreach ($arguments as $argument) {
            // Parse argument (format: "type:value")
            [$type, $value] = explode(':', $argument . ':');

            if ($type === 'role') {
                // Check role-based access
                if (!in_array($value, $userRoles)) {
                    return $this->denyAccess($request, "Role '{$value}' diperlukan untuk mengakses halaman ini.");
                }
            } elseif ($type === 'permission') {
                // Check permission-based access
                if (!in_array($value, $userPermissions)) {
                    return $this->denyAccess($request, "Permission '{$value}' diperlukan untuk mengakses halaman ini.");
                }
            } elseif ($type === 'any') {
                // Check if user has ANY of the specified roles/permissions
                $subArguments = explode('|', $value);
                $hasAccess = false;

                foreach ($subArguments as $subArg) {
                    [$subType, $subValue] = explode(':', $subArg . ':');
                    
                    if ($subType === 'role' && in_array($subValue, $userRoles)) {
                        $hasAccess = true;
                        break;
                    } elseif ($subType === 'permission' && in_array($subValue, $userPermissions)) {
                        $hasAccess = true;
                        break;
                    }
                }

                if (!$hasAccess) {
                    return $this->denyAccess($request, "Akses ditolak. Diperlukan salah satu dari: {$value}");
                }
            }
        }
    }

    /**
     * Deny access dengan logging
     *
     * @param RequestInterface $request
     * @param string $message
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    protected function denyAccess(RequestInterface $request, string $message)
    {
        // Log unauthorized access attempt
        $auditService = service('audit');
        if ($auditService) {
            $auditService->logUnauthorizedAccess(
                service('session')->get('user_id'),
                implode(', ', service('session')->get('roles') ?? []),
                current_url(),
                $message
            );
        }

        // Jika request AJAX, return JSON error
        if ($request->isAJAX()) {
            return service('response')
                ->setJSON([
                    'success' => false,
                    'message' => $message,
                ])
                ->setStatusCode(403);
        }

        // Redirect ke halaman unauthorized
        return redirect()->to('/unauthorized')
            ->with('error', $message);
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
        // Tidak ada action setelahnya
    }
}
