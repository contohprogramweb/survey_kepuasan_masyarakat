<?= $this->extend('templates/admin_layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-lock"></i> Reset Password</h5>
                </div>
                <div class="card-body">
                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= session()->getFlashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <p class="text-muted mb-4">Masukkan password baru Anda.</p>

                    <form action="<?= site_url('auth/reset-password') ?>" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="token" value="<?= esc($token) ?>">
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password Baru</label>
                            <input type="password" 
                                   class="form-control <?= session('errors.password') ? 'is-invalid' : '' ?>" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Minimal 6 karakter"
                                   required
                                   autofocus>
                            <?php if (session('errors.password')): ?>
                                <div class="invalid-feedback"><?= session('errors.password') ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirm" class="form-label">Konfirmasi Password</label>
                            <input type="password" 
                                   class="form-control <?= session('errors.password_confirm') ? 'is-invalid' : '' ?>" 
                                   id="password_confirm" 
                                   name="password_confirm" 
                                   placeholder="Ulangi password baru"
                                   required>
                            <?php if (session('errors.password_confirm')): ?>
                                <div class="invalid-feedback"><?= session('errors.password_confirm') ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-check"></i> Ubah Password
                            </button>
                        </div>
                    </form>

                    <hr>
                    
                    <div class="text-center">
                        <small>Kembali ke <a href="<?= site_url('auth/login') ?>">halaman login</a></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
