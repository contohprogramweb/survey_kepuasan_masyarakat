<?= $this->extend('templates/admin_layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-sign-in-alt"></i> Login Sistem IKM</h5>
                </div>
                <div class="card-body">
                    <div class="row justify-content-center">
                        <div class="col-md-6 col-lg-4">
                            <?php if (session()->getFlashdata('error')): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?= session()->getFlashdata('error') ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

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

                            <?php if (session()->getFlashdata('success')): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <?= session()->getFlashdata('success') ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form action="<?= site_url('auth/login') ?>" method="post">
                                <?= csrf_field() ?>
                                
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" 
                                           class="form-control <?= session('errors.username') ? 'is-invalid' : '' ?>" 
                                           id="username" 
                                           name="username" 
                                           value="<?= old('username') ?>"
                                           placeholder="Masukkan username"
                                           required
                                           autofocus>
                                    <?php if (session('errors.username')): ?>
                                        <div class="invalid-feedback"><?= session('errors.username') ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" 
                                           class="form-control <?= session('errors.password') ? 'is-invalid' : '' ?>" 
                                           id="password" 
                                           name="password" 
                                           placeholder="Masukkan password"
                                           required>
                                    <?php if (session('errors.password')): ?>
                                        <div class="invalid-feedback"><?= session('errors.password') ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                    <label class="form-check-label" for="remember">Ingat saya</label>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-sign-in-alt"></i> Login
                                    </button>
                                </div>

                                <hr>

                                <div class="text-center">
                                    <small>Lupa password? <a href="<?= site_url('auth/forgot-password') ?>">Reset di sini</a></small>
                                </div>
                            </form>

                            <?php if (config('OAuth2')->enabled ?? false): ?>
                            <hr>
                            <div class="text-center">
                                <small>Atau login dengan:</small>
                            </div>
                            <div class="d-grid gap-2 mt-2">
                                <a href="<?= site_url('auth/oauth/google') ?>" class="btn btn-outline-danger">
                                    <i class="fab fa-google"></i> Google
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
