<?= $this->extend('templates/admin_layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0 text-gray-800"><?= esc($title) ?></h1>
                <a href="<?= site_url('admin/periods') ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Tambah Periode Baru</h6>
        </div>
        <div class="card-body">
            <?php if (isset($validation) && $validation->getErrors()): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($validation->getErrors() as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="<?= site_url('admin/periods/create') ?>" method="post">
                <?= csrf_field() ?>
                
                <div class="mb-3">
                    <label for="nama_periode" class="form-label">Nama Periode <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control <?= session('errors.nama_periode') ? 'is-invalid' : '' ?>" 
                           id="nama_periode" 
                           name="nama_periode" 
                           value="<?= old('nama_periode') ?>"
                           placeholder="Contoh: Periode Januari - Maret 2024"
                           required>
                    <div class="invalid-feedback">
                        <?= session('errors.nama_periode') ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_mulai" class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                        <input type="date" 
                               class="form-control <?= session('errors.tanggal_mulai') ? 'is-invalid' : '' ?>" 
                               id="tanggal_mulai" 
                               name="tanggal_mulai" 
                               value="<?= old('tanggal_mulai') ?>"
                               required>
                        <div class="invalid-feedback">
                            <?= session('errors.tanggal_mulai') ?>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_selesai" class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                        <input type="date" 
                               class="form-control <?= session('errors.tanggal_selesai') ? 'is-invalid' : '' ?>" 
                               id="tanggal_selesai" 
                               name="tanggal_selesai" 
                               value="<?= old('tanggal_selesai') ?>"
                               required>
                        <div class="invalid-feedback">
                            <?= session('errors.tanggal_selesai') ?>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <textarea class="form-control <?= session('errors.deskripsi') ? 'is-invalid' : '' ?>" 
                              id="deskripsi" 
                              name="deskripsi" 
                              rows="3"
                              placeholder="Deskripsi periode survei (opsional)"><?= old('deskripsi') ?></textarea>
                    <div class="invalid-feedback">
                        <?= session('errors.deskripsi') ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select <?= session('errors.status') ? 'is-invalid' : '' ?>" 
                            id="status" 
                            name="status">
                        <option value="nonaktif" <?= old('status') === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                        <option value="aktif" <?= old('status') === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                    </select>
                    <div class="invalid-feedback">
                        <?= session('errors.status') ?>
                    </div>
                    <small class="text-muted">Hanya satu periode yang dapat aktif pada satu waktu.</small>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Simpan Periode
                    </button>
                    <a href="<?= site_url('admin/periods') ?>" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Set minimum date untuk tanggal selesai berdasarkan tanggal mulai
document.getElementById('tanggal_mulai').addEventListener('change', function() {
    const startDate = this.value;
    const endDateInput = document.getElementById('tanggal_selesai');
    if (startDate) {
        endDateInput.min = startDate;
        if (endDateInput.value && endDateInput.value < startDate) {
            endDateInput.value = startDate;
        }
    }
});
</script>

<?= $this->endSection() ?>
