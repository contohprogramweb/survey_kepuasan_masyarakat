<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

/**
 * Auth Filter - Middleware untuk autentikasi
 * Mencegah akses ke halaman yang memerlukan login
 */
class AuthFilter implements FilterInterface
{
    /**
     * Cek apakah user sudah authenticated sebelum mengakses halaman
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = service('session');

        if (!$session->has('user_id')) {
            // Jika request AJAX, return JSON error
            if ($request->isAJAX()) {
                return service('response')
                    ->setJSON([
                        'success' => false,
                        'message' => 'Unauthorized. Silakan login.',
                        'redirect' => site_url('auth/login'),
                    ])
                    ->setStatusCode(401);
            }

            // Redirect ke halaman login
            return redirect()->to('auth/login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        // Cek session expiry
        $lastActivity = $session->get('last_activity');
        if ($lastActivity && (time() - $lastActivity) > config('App')->sessionExpiration) {
            $session->destroy();
            return redirect()->to('auth/login')
                ->with('error', 'Sesi Anda telah habis. Silakan login kembali.');
        }

        // Update last activity
        $session->set('last_activity', time());
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method runs after all controllers have
     * executed their task and also after any applicable "After" filters.
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
