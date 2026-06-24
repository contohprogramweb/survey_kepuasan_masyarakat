<?= $this->extend('templates/admin_layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <h4><i class="fas fa-cog"></i> <?= esc($title) ?></h4>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form action="<?= site_url('admin/settings') ?>" method="post">
        <?= csrf_field() ?>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fas fa-info-circle"></i> Pengaturan Umum</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="app_name" class="form-label">Nama Aplikasi</label>
                            <input type="text" class="form-control" id="app_name" name="app_name" 
                                   value="<?= esc($settings['app_name']) ?>">
                        </div>

                        <div class="mb-3">
                            <label for="default_language" class="form-label">Bahasa Default</label>
                            <select class="form-select" id="default_language" name="default_language">
                                <option value="id" <?= $settings['default_language'] === 'id' ? 'selected' : '' ?>>Indonesia</option>
                                <option value="en" <?= $settings['default_language'] === 'en' ? 'selected' : '' ?>>English</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="timezone" class="form-label">Zona Waktu</label>
                            <select class="form-select" id="timezone" name="timezone">
                                <option value="Asia/Jakarta" <?= $settings['timezone'] === 'Asia/Jakarta' ? 'selected' : '' ?>>WIB (Jakarta)</option>
                                <option value="Asia/Makassar" <?= $settings['timezone'] === 'Asia/Makassar' ? 'selected' : '' ?>>WITA (Makassar)</option>
                                <option value="Asia/Jayapura" <?= $settings['timezone'] === 'Asia/Jayapura' ? 'selected' : '' ?>>WIT (Jayapura)</option>
                            </select>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode" 
                                   value="1" <?= $settings['maintenance_mode'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="maintenance_mode">Mode Maintenance</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="fas fa-shield-alt"></i> Keamanan & Autentikasi</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="enable_mfa" name="enable_mfa" 
                                   value="1" <?= $settings['enable_mfa'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="enable_mfa">Aktifkan MFA (Two-Factor Auth)</label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="enable_oauth" name="enable_oauth" 
                                   value="1" <?= $settings['enable_oauth'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="enable_oauth">Aktifkan OAuth (Google Login)</label>
                        </div>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-poll"></i> Pengaturan Survei</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="survey_allow_anonymous" name="survey_allow_anonymous" 
                                   value="1" <?= $settings['survey_allow_anonymous'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="survey_allow_anonymous">Izinkan Responden Anonim</label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="survey_require_consent" name="survey_require_consent" 
                                   value="1" <?= $settings['survey_require_consent'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="survey_require_consent">Wajib Persetujuan PDP</label>
                        </div>

                        <div class="mb-3">
                            <label for="data_retention_days" class="form-label">Retensi Data (hari)</label>
                            <input type="number" class="form-control" id="data_retention_days" name="data_retention_days" 
                                   value="<?= esc($settings['data_retention_days']) ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Simpan Pengaturan
                </button>
            </div>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
