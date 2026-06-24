<?= $this->extend('templates/admin_layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h4><i class="fas fa-users"></i> <?= esc($title) ?></h4>
            <a href="<?= site_url('admin/users/new') ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Pengguna
            </a>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="usersTable">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>Unit</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= esc($user['id_pengguna']) ?></td>
                            <td><?= esc($user['username']) ?></td>
                            <td><?= esc($user['nama_lengkap']) ?></td>
                            <td><?= esc($user['email']) ?></td>
                            <td><?= esc($user['nama_unit'] ?? '-') ?></td>
                            <td>
                                <span class="badge bg-<?= $user['role'] === 'super_admin' ? 'danger' : ($user['role'] === 'admin_unit' ? 'warning' : 'info') ?>">
                                    <?= esc($user['role']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $user['is_active'] ? 'success' : 'secondary' ?>" id="status-<?= $user['id_pengguna'] ?>">
                                    <?= $user['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                </span>
                            </td>
                            <td><?= $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : '-' ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= site_url('admin/users/' . $user['id_pengguna'] . '/edit') ?>" class="btn btn-info" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-warning btn-toggle-status" data-id="<?= $user['id_pengguna'] ?>" title="Toggle Status">
                                        <i class="fas fa-sync"></i>
                                    </button>
                                    <a href="<?= site_url('admin/users/' . $user['id_pengguna'] . '/reset-password') ?>" class="btn btn-secondary" title="Reset Password" onclick="return confirm('Reset password pengguna ini?')">
                                        <i class="fas fa-key"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger btn-delete-user" data-id="<?= $user['id_pengguna'] ?>" data-name="<?= esc($user['nama_lengkap']) ?>" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle status
    document.querySelectorAll('.btn-toggle-status').forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = this.dataset.id;
            if (confirm('Toggle status pengguna ini?')) {
                fetch(`<?= site_url('admin/users') ?>/${userId}/toggle-status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const badge = document.getElementById(`status-${userId}`);
                        if (data.is_active) {
                            badge.className = 'badge bg-success';
                            badge.textContent = 'Aktif';
                        } else {
                            badge.className = 'badge bg-secondary';
                            badge.textContent = 'Nonaktif';
                        }
                        alert(data.message);
                    } else {
                        alert(data.message);
                    }
                });
            }
        });
    });

    // Delete user
    document.querySelectorAll('.btn-delete-user').forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = this.dataset.id;
            const userName = this.dataset.name;
            if (confirm(`Hapus pengguna "${userName}"? Tindakan ini tidak dapat dibatalkan.`)) {
                window.location.href = `<?= site_url('admin/users') ?>/${userId}`;
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
