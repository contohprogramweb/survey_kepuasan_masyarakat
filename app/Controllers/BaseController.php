<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController dengan middleware auth, RBAC, dan CSRF
 * Base controller untuk semua controller di aplikasi IKM
 */
class BaseController extends Controller
{
    /**
     * Instance dari Services.
     *
     * @var \Config\Services
     */
    protected $services;

    /**
     * User yang sedang login
     *
     * @var object|null
     */
    protected $user = null;

    /**
     * Permissions user yang sedang login
     *
     * @var array
     */
    protected $permissions = [];

    /**
     * Roles user yang sedang login
     *
     * @var array
     */
    protected $roles = [];

    /**
     * Constructor dengan initialization filters
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param LoggerInterface $logger
     */
    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ) {
        // DO NOT EDIT BELOW THIS LINE
        parent::initController($request, $response, $logger);

        // Initialize services
        $this->services = service('services');

        // Setup CSRF protection secara global
        $this->setupCSRF();

        // Load user data jika sudah authenticated
        $this->loadUserData();
    }

    /**
     * Setup CSRF Protection untuk semua request
     */
    protected function setupCSRF(): void
    {
        $csrf = config('Security');

        if ($csrf->csrfProtection === 'cookie') {
            $tokenName = $csrf->tokenName;
            $headerName = $csrf->headerName;

            // Set CSRF token di response header untuk AJAX requests
            if (service('request')->isAJAX()) {
                $this->response->setHeader($headerName, csrf_hash());
            }
        }
    }

    /**
     * Load data user yang sedang login
     */
    protected function loadUserData(): void
    {
        $session = service('session');

        if ($session->has('user_id')) {
            $this->user = [
                'id' => $session->get('user_id'),
                'username' => $session->get('username'),
                'email' => $session->get('email'),
                'full_name' => $session->get('full_name'),
                'nip' => $session->get('nip'),
                'unit_kerja' => $session->get('unit_kerja'),
            ];

            $this->roles = $session->get('roles') ?? [];
            $this->permissions = $session->get('permissions') ?? [];

            // Share ke views
            $this->data['current_user'] = $this->user;
            $this->data['user_roles'] = $this->roles;
            $this->data['user_permissions'] = $this->permissions;
        }
    }

    /**
     * Cek apakah user sudah authenticated
     *
     * @return bool
     */
    protected function isAuthenticated(): bool
    {
        return service('session')->has('user_id');
    }

    /**
     * Require authentication - redirect ke login jika belum login
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|void
     */
    protected function requireAuth()
    {
        if (!$this->isAuthenticated()) {
            return redirect()->to('/auth/login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }
    }

    /**
     * Require permission - cek apakah user memiliki permission tertentu
     *
     * @param string|array $permissions Permission yang dibutuhkan
     * @param string $redirectUrl URL redirect jika tidak authorized
     * @return \CodeIgniter\HTTP\RedirectResponse|void
     */
    protected function requirePermission($permissions, string $redirectUrl = '/unauthorized')
    {
        $this->requireAuth();

        $permissions = is_array($permissions) ? $permissions : [$permissions];

        $hasPermission = false;
        foreach ($permissions as $permission) {
            if (in_array($permission, $this->permissions)) {
                $hasPermission = true;
                break;
            }
        }

        if (!$hasPermission) {
            // Log unauthorized access attempt
            service('audit')->logUnauthorizedAccess(
                $this->user['id'] ?? null,
                implode(', ', $permissions),
                current_url()
            );

            return redirect()->to($redirectUrl)
                ->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }
    }

    /**
     * Require role - cek apakah user memiliki role tertentu
     *
     * @param string|array $roles Role yang dibutuhkan
     * @param string $redirectUrl URL redirect jika tidak authorized
     * @return \CodeIgniter\HTTP\RedirectResponse|void
     */
    protected function requireRole($roles, string $redirectUrl = '/unauthorized')
    {
        $this->requireAuth();

        $roles = is_array($roles) ? $roles : [$roles];

        $hasRole = false;
        foreach ($roles as $role) {
            if (in_array($role, $this->roles)) {
                $hasRole = true;
                break;
            }
        }

        if (!$hasRole) {
            // Log unauthorized access attempt
            service('audit')->logUnauthorizedAccess(
                $this->user['id'] ?? null,
                'role:' . implode(', ', $roles),
                current_url()
            );

            return redirect()->to($redirectUrl)
                ->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }
    }

    /**
     * Render view dengan layout
     *
     * @param string $view Nama view
     * @param array $data Data untuk view
     * @param string|null $layout Layout yang digunakan (null untuk tanpa layout)
     * @return string
     */
    protected function render(string $view, array $data = [], ?string $layout = 'admin'): string
    {
        $this->data = array_merge($this->data ?? [], $data);

        if ($layout === null) {
            return view($view, $this->data);
        }

        $this->data['content'] = view($view, $this->data);
        return view("layouts/{$layout}", $this->data);
    }

    /**
     * Return JSON response dengan format standar
     *
     * @param mixed $data
     * @param int $statusCode
     * @param string $message
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    protected function respondJSON(
        mixed $data,
        int $statusCode = 200,
        string $message = 'success'
    ): \CodeIgniter\HTTP\ResponseInterface {
        return $this->response->setJSON([
            'success' => $statusCode >= 200 && $statusCode < 300,
            'message' => $message,
            'data' => $data,
            'timestamp' => time(),
        ])->setStatusCode($statusCode);
    }

    /**
     * Return error JSON response
     *
     * @param string $message
     * @param int $statusCode
     * @param mixed $errors
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    protected function respondError(
        string $message = 'error',
        int $statusCode = 400,
        mixed $errors = null
    ): \CodeIgniter\HTTP\ResponseInterface {
        return $this->response->setJSON([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'timestamp' => time(),
        ])->setStatusCode($statusCode);
    }
}
