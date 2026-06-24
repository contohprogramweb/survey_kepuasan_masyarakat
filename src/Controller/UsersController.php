<?php

namespace App\Controller;

use App\Model\UserModel;
use App\Service\AuditLogService;

/**
 * UsersController
 * 
 * Berdasarkan SRS F-01 dan UC-02
 * Manajemen Pengguna: CRUD, Role Assignment, Password Reset, Aktivasi/Deaktivasi
 */
class UsersController
{
    private \PDO $db;
    private UserModel $userModel;
    private AuditLogService $auditLog;
    
    public function __construct(\PDO $db)
    {
        $this->db = $db;
        $this->userModel = new UserModel($db);
        $this->auditLog = new AuditLogService($db);
    }
    
    /**
     * Display user list page
     */
    public function index(): void
    {
        // Check permission (Super Admin only for full access)
        $this->checkPermission(['super_admin', 'admin']);
        
        include __DIR__ . '/../../templates/users/index.php';
    }
    
    /**
     * Get users data for DataTables server-side processing
     */
    public function getData(): void
    {
        header('Content-Type: application/json');
        
        $this->checkPermission(['super_admin', 'admin']);
        
        // Get DataTables parameters
        $draw = isset($_GET['draw']) ? (int) $_GET['draw'] : 1;
        $start = isset($_GET['start']) ? (int) $_GET['start'] : 0;
        $length = isset($_GET['length']) ? (int) $_GET['length'] : 10;
        
        // Order
        $orderColumn = isset($_GET['order'][0]['column']) ? (int) $_GET['order'][0]['column'] : 0;
        $orderDir = isset($_GET['order'][0]['dir']) ? $_GET['order'][0]['dir'] : 'asc';
        $order = ['column' => $orderColumn, 'dir' => $orderDir];
        
        // Search
        $searchValue = isset($_GET['search']['value']) ? $_GET['search']['value'] : '';
        $search = ['value' => $searchValue];
        
        // Filters
        $roleFilter = isset($_GET['role']) ? $_GET['role'] : null;
        $statusFilter = isset($_GET['status']) ? $_GET['status'] : null;
        
        // Get data from model
        $result = $this->userModel->getUsersForDataTable(
            $draw,
            $start,
            $length,
            $order,
            $search,
            $roleFilter,
            $statusFilter
        );
        
        // Format data for DataTables
        foreach ($result['data'] as &$user) {
            $user['actions'] = $this->renderActions($user);
            $user['role_badge'] = $this->getRoleBadge($user['role']);
            $user['status_badge'] = $this->getStatusBadge($user['status']);
            $user['created_at_formatted'] = date('d M Y H:i', strtotime($user['created_at']));
        }
        
        echo json_encode($result);
    }
    
    /**
     * Show create user form
     */
    public function create(): void
    {
        $this->checkPermission(['super_admin']);
        
        $roles = UserModel::ROLES;
        $statuses = [
            UserModel::STATUS_ACTIVE => 'Active',
            UserModel::STATUS_INACTIVE => 'Inactive',
        ];
        
        include __DIR__ . '/../../templates/users/create.php';
    }
    
    /**
     * Store new user
     */
    public function store(): void
    {
        header('Content-Type: application/json');
        
        $this->checkPermission(['super_admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        // Get POST data
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        $role = $_POST['role'] ?? UserModel::ROLE_OPERATOR;
        $status = $_POST['status'] ?? UserModel::STATUS_ACTIVE;
        $mfaEnabled = isset($_POST['mfa_enabled']) && $_POST['mfa_enabled'] === 'on';
        
        // Validation
        $errors = [];
        
        if (empty($username)) {
            $errors[] = 'Username wajib diisi';
        } elseif (strlen($username) < 3) {
            $errors[] = 'Username minimal 3 karakter';
        }
        
        if (empty($email)) {
            $errors[] = 'Email wajib diisi';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid';
        }
        
        if (empty($fullName)) {
            $errors[] = 'Nama lengkap wajib diisi';
        }
        
        if (empty($password)) {
            $errors[] = 'Password wajib diisi';
        } elseif (strlen($password) < 8) {
            $errors[] = 'Password minimal 8 karakter';
        }
        
        if ($password !== $passwordConfirm) {
            $errors[] = 'Konfirmasi password tidak cocok';
        }
        
        // Check unique username
        if ($this->userModel->usernameExists($username)) {
            $errors[] = 'Username sudah digunakan';
        }
        
        // Check unique email
        if ($this->userModel->emailExists($email)) {
            $errors[] = 'Email sudah digunakan';
        }
        
        // Validate role
        if (!in_array($role, array_keys(UserModel::ROLES))) {
            $errors[] = 'Role tidak valid';
        }
        
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $errors
            ]);
            return;
        }
        
        // Create user
        try {
            $userId = $this->userModel->create([
                'username' => $username,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_BCRYPT),
                'full_name' => $fullName,
                'role' => $role,
                'status' => $status,
                'mfa_enabled' => $mfaEnabled,
                'created_by' => $this->getCurrentUserId(),
            ]);
            
            // Log audit
            $this->auditLog->logUserCreate($this->getCurrentUserId(), $userId, [
                'username' => $username,
                'email' => $email,
                'full_name' => $fullName,
                'role' => $role,
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'User berhasil ditambahkan',
                'user_id' => $userId
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Gagal menambahkan user: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Show edit user form
     */
    public function edit(int $id): void
    {
        $this->checkPermission(['super_admin']);
        
        if (!$this->userModel->loadById($id)) {
            http_response_code(404);
            die('User tidak ditemukan');
        }
        
        $user = $this->userModel->getData();
        $roles = UserModel::ROLES;
        $statuses = [
            UserModel::STATUS_ACTIVE => 'Active',
            UserModel::STATUS_INACTIVE => 'Inactive',
            UserModel::STATUS_SUSPENDED => 'Suspended',
        ];
        
        include __DIR__ . '/../../templates/users/edit.php';
    }
    
    /**
     * Update user
     */
    public function update(int $id): void
    {
        header('Content-Type: application/json');
        
        $this->checkPermission(['super_admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        if (!$this->userModel->loadById($id)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'User tidak ditemukan']);
            return;
        }
        
        $oldData = $this->userModel->getData();
        
        // Get POST data
        $email = trim($_POST['email'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $role = $_POST['role'] ?? UserModel::ROLE_OPERATOR;
        $status = $_POST['status'] ?? UserModel::STATUS_ACTIVE;
        $mfaEnabled = isset($_POST['mfa_enabled']) && $_POST['mfa_enabled'] === 'on';
        
        // Validation
        $errors = [];
        
        if (empty($email)) {
            $errors[] = 'Email wajib diisi';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid';
        }
        
        if (empty($fullName)) {
            $errors[] = 'Nama lengkap wajib diisi';
        }
        
        // Check unique email (excluding current user)
        if ($this->userModel->emailExists($email, $id)) {
            $errors[] = 'Email sudah digunakan';
        }
        
        // Validate role
        if (!in_array($role, array_keys(UserModel::ROLES))) {
            $errors[] = 'Role tidak valid';
        }
        
        // Cannot change Super Admin role to other role
        if ($oldData['role'] === UserModel::ROLE_SUPER_ADMIN && $role !== UserModel::ROLE_SUPER_ADMIN) {
            $errors[] = 'Role Super Admin tidak dapat diubah';
        }
        
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $errors
            ]);
            return;
        }
        
        // Update user
        try {
            $updateData = [
                'email' => $email,
                'full_name' => $fullName,
                'role' => $role,
                'status' => $status,
                'mfa_enabled' => $mfaEnabled,
                'updated_by' => $this->getCurrentUserId(),
            ];
            
            $this->userModel->update($id, $updateData);
            
            // Log audit
            $this->auditLog->logUserUpdate($this->getCurrentUserId(), $id, $oldData, $updateData);
            
            // Log role change if changed
            if ($oldData['role'] !== $role) {
                $this->auditLog->logRoleChange($this->getCurrentUserId(), $id, $oldData['role'], $role);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'User berhasil diupdate'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Gagal mengupdate user: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Show user detail
     */
    public function show(int $id): void
    {
        $this->checkPermission(['super_admin', 'admin']);
        
        if (!$this->userModel->loadById($id)) {
            http_response_code(404);
            die('User tidak ditemukan');
        }
        
        $user = $this->userModel->getData();
        $auditLogs = $this->auditLog->getLogsForEntity('user', $id, 10);
        
        include __DIR__ . '/../../templates/users/show.php';
    }
    
    /**
     * Delete user (soft delete)
     */
    public function destroy(int $id): void
    {
        header('Content-Type: application/json');
        
        $this->checkPermission(['super_admin']);
        
        if (!$this->userModel->loadById($id)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'User tidak ditemukan']);
            return;
        }
        
        // Cannot delete Super Admin
        if ($this->userModel->isSuperAdmin($id)) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Super Admin tidak dapat dihapus, hanya dapat dinonaktifkan'
            ]);
            return;
        }
        
        try {
            $this->userModel->softDelete($id, $this->getCurrentUserId());
            
            // Log audit
            $this->auditLog->logSoftDelete($this->getCurrentUserId(), $id);
            
            echo json_encode([
                'success' => true,
                'message' => 'User berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Gagal menghapus user: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Reset user password
     */
    public function resetPassword(int $id): void
    {
        header('Content-Type: application/json');
        
        // Admin+ can reset password
        $this->checkPermission(['super_admin', 'admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        if (!$this->userModel->loadById($id)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'User tidak ditemukan']);
            return;
        }
        
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validation
        $errors = [];
        
        if (empty($newPassword)) {
            $errors[] = 'Password baru wajib diisi';
        } elseif (strlen($newPassword) < 8) {
            $errors[] = 'Password minimal 8 karakter';
        }
        
        if ($newPassword !== $confirmPassword) {
            $errors[] = 'Konfirmasi password tidak cocok';
        }
        
        // Cannot reset Super Admin password unless you're Super Admin
        if ($this->userModel->isSuperAdmin($id) && !$this->hasRole(['super_admin'])) {
            $errors[] = 'Hanya Super Admin yang dapat mereset password Super Admin lain';
        }
        
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $errors
            ]);
            return;
        }
        
        try {
            $this->userModel->resetPassword(
                $id,
                password_hash($newPassword, PASSWORD_BCRYPT),
                $this->getCurrentUserId()
            );
            
            // Log audit
            $this->auditLog->logPasswordReset($this->getCurrentUserId(), $id);
            
            echo json_encode([
                'success' => true,
                'message' => 'Password berhasil direset'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Gagal mereset password: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Activate user account
     */
    public function activate(int $id): void
    {
        header('Content-Type: application/json');
        
        $this->checkPermission(['super_admin', 'admin']);
        
        if (!$this->userModel->loadById($id)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'User tidak ditemukan']);
            return;
        }
        
        try {
            $this->userModel->activate($id, $this->getCurrentUserId());
            
            // Log audit
            $this->auditLog->logActivate($this->getCurrentUserId(), $id);
            
            echo json_encode([
                'success' => true,
                'message' => 'User berhasil diaktifkan'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Gagal mengaktifkan user: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Deactivate user account
     */
    public function deactivate(int $id): void
    {
        header('Content-Type: application/json');
        
        $this->checkPermission(['super_admin', 'admin']);
        
        if (!$this->userModel->loadById($id)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'User tidak ditemukan']);
            return;
        }
        
        // Cannot deactivate Super Admin
        if ($this->userModel->isSuperAdmin($id)) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Super Admin tidak dapat dinonaktifkan'
            ]);
            return;
        }
        
        try {
            $this->userModel->deactivate($id, $this->getCurrentUserId());
            
            // Log audit
            $this->auditLog->logDeactivate($this->getCurrentUserId(), $id);
            
            echo json_encode([
                'success' => true,
                'message' => 'User berhasil dinonaktifkan'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Gagal menonaktifkan user: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Render action buttons for DataTables
     */
    private function renderActions(array $user): string
    {
        $actions = [];
        $currentRole = $this->getCurrentUserRole();
        
        // View button
        $actions[] = '<button class="btn btn-sm btn-info view-btn" data-id="' . $user['id'] . '" title="Detail"><i class="fas fa-eye"></i></button>';
        
        // Edit button (Super Admin only)
        if ($currentRole === 'super_admin') {
            $actions[] = '<button class="btn btn-sm btn-warning edit-btn" data-id="' . $user['id'] . '" title="Edit"><i class="fas fa-edit"></i></button>';
        }
        
        // Reset Password (Admin+)
        if (in_array($currentRole, ['super_admin', 'admin'])) {
            $actions[] = '<button class="btn btn-sm btn-secondary reset-password-btn" data-id="' . $user['id'] . '" title="Reset Password"><i class="fas fa-key"></i></button>';
        }
        
        // Activate/Deactivate (Admin+)
        if (in_array($currentRole, ['super_admin', 'admin']) && $user['role'] !== 'super_admin') {
            if ($user['status'] === 'active') {
                $actions[] = '<button class="btn btn-sm btn-danger deactivate-btn" data-id="' . $user['id'] . '" title="Nonaktifkan"><i class="fas fa-ban"></i></button>';
            } else {
                $actions[] = '<button class="btn btn-sm btn-success activate-btn" data-id="' . $user['id'] . '" title="Aktifkan"><i class="fas fa-check"></i></button>';
            }
        }
        
        // Delete (Super Admin only, not for Super Admin users)
        if ($currentRole === 'super_admin' && $user['role'] !== 'super_admin') {
            $actions[] = '<button class="btn btn-sm btn-danger delete-btn" data-id="' . $user['id'] . '" title="Hapus"><i class="fas fa-trash"></i></button>';
        }
        
        return implode(' ', $actions);
    }
    
    /**
     * Get role badge HTML
     */
    private function getRoleBadge(string $role): string
    {
        $colors = [
            'super_admin' => 'danger',
            'admin' => 'warning',
            'operator' => 'info',
            'pimpinan' => 'primary',
            'dpo' => 'purple',
            'devops' => 'success',
        ];
        
        $color = $colors[$role] ?? 'secondary';
        $label = UserModel::ROLES[$role] ?? $role;
        
        return '<span class="badge badge-' . $color . '">' . $label . '</span>';
    }
    
    /**
     * Get status badge HTML
     */
    private function getStatusBadge(string $status): string
    {
        $colors = [
            'active' => 'success',
            'inactive' => 'secondary',
            'suspended' => 'danger',
        ];
        
        $color = $colors[$status] ?? 'secondary';
        $label = ucfirst($status);
        
        return '<span class="badge badge-' . $color . '">' . $label . '</span>';
    }
    
    /**
     * Check if current user has required permission
     */
    private function checkPermission(array $allowedRoles): void
    {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            die('Unauthorized');
        }
        
        $userRole = $_SESSION['user_role'] ?? '';
        
        if (!in_array($userRole, $allowedRoles)) {
            http_response_code(403);
            die('Forbidden: Insufficient permissions');
        }
    }
    
    /**
     * Check if current user has specific role
     */
    private function hasRole(array $roles): bool
    {
        session_start();
        $userRole = $_SESSION['user_role'] ?? '';
        return in_array($userRole, $roles);
    }
    
    /**
     * Get current user ID
     */
    private function getCurrentUserId(): int
    {
        session_start();
        return $_SESSION['user_id'] ?? 0;
    }
    
    /**
     * Get current user role
     */
    private function getCurrentUserRole(): string
    {
        session_start();
        return $_SESSION['user_role'] ?? '';
    }
}
