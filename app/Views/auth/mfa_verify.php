<?= $this->extend('templates/admin_layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-shield-alt"></i> Verifikasi MFA</h5>
                </div>
                <div class="card-body">
                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= session()->getFlashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <p class="text-center mb-4">Masukkan kode 6 digit dari aplikasi authenticator Anda.</p>

                    <form action="<?= site_url('auth/mfa/verify') ?>" method="post">
                        <?= csrf_field() ?>
                        
                        <div class="mb-3">
                            <label for="code" class="form-label">Kode MFA</label>
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
                            <button type="submit" class="btn btn-warning btn-lg">
                                <i class="fas fa-check-circle"></i> Verifikasi
                            </button>
                        </div>
                    </form>

                    <hr>
                    
                    <div class="text-center">
                        <small class="text-muted">
                            Tidak punya akses ke authenticator? <br>
                            <a href="#" class="text-warning">Hubungi administrator</a>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-submit ketika 6 digit terisi
document.getElementById('code').addEventListener('input', function(e) {
    if (this.value.length === 6) {
        // Optional: auto submit
        // e.target.form.submit();
    }
});
</script>
<?= $this->endSection() ?>
