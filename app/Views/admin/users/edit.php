<?= $this->extend('templates/admin_layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <h1 class="h3"><?= esc($title) ?></h1>
        </div>
    </div>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form action="<?= site_url('admin/users/update/' . $user['id_pengguna']) ?>" method="post">
                <?= csrf_field() ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= session('errors.username') ? 'is-invalid' : '' ?>" 
                                   id="username" name="username" value="<?= old('username', $user['username']) ?>" required>
                            <?php if (session('errors.username')): ?>
                                <div class="invalid-feedback"><?= esc(session('errors.username')) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control <?= session('errors.email') ? 'is-invalid' : '' ?>" 
                                   id="email" name="email" value="<?= old('email', $user['email']) ?>" required>
                            <?php if (session('errors.email')): ?>
                                <div class="invalid-feedback"><?= esc(session('errors.email')) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password Baru</label>
                            <input type="password" class="form-control <?= session('errors.password') ? 'is-invalid' : '' ?>" 
                                   id="password" name="password" minlength="6">
                            <div class="form-text">Kosongkan jika tidak ingin mengubah password. Minimal 6 karakter.</div>
                            <?php if (session('errors.password')): ?>
                                <div class="invalid-feedback"><?= esc(session('errors.password')) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="nama_lengkap" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= session('errors.nama_lengkap') ? 'is-invalid' : '' ?>" 
                                   id="nama_lengkap" name="nama_lengkap" value="<?= old('nama_lengkap', $user['nama_lengkap']) ?>" required>
                            <?php if (session('errors.nama_lengkap')): ?>
                                <div class="invalid-feedback"><?= esc(session('errors.nama_lengkap')) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select <?= session('errors.role') ? 'is-invalid' : '' ?>" 
                                    id="role" name="role" required>
                                <option value="">Pilih Role</option>
                                <option value="super_admin" <?= old('role', $user['role']) == 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                                <option value="admin_unit" <?= old('role', $user['role']) == 'admin_unit' ? 'selected' : '' ?>>Admin Unit</option>
                                <option value="operator" <?= old('role', $user['role']) == 'operator' ? 'selected' : '' ?>>Operator</option>
                                <option value="viewer" <?= old('role', $user['role']) == 'viewer' ? 'selected' : '' ?>>Viewer</option>
                            </select>
                            <?php if (session('errors.role')): ?>
                                <div class="invalid-feedback"><?= esc(session('errors.role')) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="id_unit" class="form-label">Unit Layanan <span class="text-danger">*</span></label>
                            <select class="form-select <?= session('errors.id_unit') ? 'is-invalid' : '' ?>" 
                                    id="id_unit" name="id_unit" required>
                                <option value="">Pilih Unit</option>
                                <!-- Unit akan di-load dari database -->
                            </select>
                            <?php if (session('errors.id_unit')): ?>
                                <div class="invalid-feedback"><?= esc(session('errors.id_unit')) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= site_url('admin/users') ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Pengguna
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
