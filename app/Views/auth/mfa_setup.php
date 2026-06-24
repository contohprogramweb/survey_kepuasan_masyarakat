<?= $this->extend('templates/admin_layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-mobile-alt"></i> Setup Two-Factor Authentication</h5>
                </div>
                <div class="card-body">
                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= session()->getFlashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="alert alert-info">
                        <strong>Langkah 1:</strong> Install aplikasi Google Authenticator atau Authy di smartphone Anda.
                    </div>

                    <div class="text-center mb-4">
                        <img src="<?= esc($qrCodeUrl) ?>" alt="QR Code MFA" class="img-thumbnail" style="max-width: 250px;">
                        <p class="mt-2 text-muted">Scan QR code di atas dengan aplikasi authenticator</p>
                    </div>

                    <div class="alert alert-warning">
                        <strong>Secret Key:</strong> <code class="fs-5"><?= esc($secret) ?></code>
                        <br><small>Gunakan key ini jika tidak bisa scan QR code</small>
                    </div>

                    <form action="<?= site_url('auth/mfa/setup') ?>" method="post">
                        <?= csrf_field() ?>
                        
                        <div class="mb-3">
                            <label for="code" class="form-label">Kode Verifikasi dari Aplikasi</label>
                            <input type="text" 
                                   class="form-control form-control-lg text-center <?= session('errors.code') ? 'is-invalid' : '' ?>" 
                                   id="code" 
                                   name="code" 
                                   placeholder="000000"
                                   maxlength="6"
                                   pattern="[0-9]{6}"
                                   required
                                   autofocus>
                            <?php if (session('errors.code')): ?>
                                <div class="invalid-feedback"><?= session('errors.code') ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-info btn-lg">
                                <i class="fas fa-check-circle"></i> Aktifkan MFA
                            </button>
                        </div>
                    </form>

                    <hr>
                    
                    <div class="text-center">
                        <small class="text-muted">
                            Setelah diaktifkan, Anda akan diminta memasukkan kode MFA setiap kali login.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
