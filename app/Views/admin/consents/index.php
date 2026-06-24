<?= $this->extend('templates/admin_layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <h1 class="h3"><?= esc($title) ?></h1>
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

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered" id="consentsTable">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="15%">Tanggal</th>
                            <th width="20%">Responden</th>
                            <th width="15%">Unit Layanan</th>
                            <th width="15%">Status Persetujuan</th>
                            <th width="15%">Tanggal Kadaluarsa</th>
                            <th width="15%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                <p class="text-muted">Belum ada data persetujuan. Data akan muncul setelah responden menyetujui pemrosesan data.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Statistik Persetujuan</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="border rounded p-3 text-center">
                                <h3 class="text-success mb-0">0</h3>
                                <small class="text-muted">Disetujui</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="border rounded p-3 text-center">
                                <h3 class="text-danger mb-0">0</h3>
                                <small class="text-muted">Ditolak</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="border rounded p-3 text-center">
                                <h3 class="text-warning mb-0">0</h3>
                                <small class="text-muted">Kadaluarsa</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="border rounded p-3 text-center">
                                <h3 class="text-primary mb-0">0</h3>
                                <small class="text-muted">Total</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informasi PDP</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Masa Berlaku Persetujuan:</strong> 2 tahun sejak tanggal persetujuan</p>
                    <p class="mb-2"><strong>Data yang Dikumpulkan:</strong></p>
                    <ul class="small mb-2">
                        <li>Informasi demografis responden</li>
                        <li>Jawaban survei kepuasan</li>
                        <li>Timestamp dan metadata pengisian</li>
                    </ul>
                    <p class="mb-0"><strong>Hak Responden:</strong></p>
                    <ul class="small mb-0">
                        <li>Mengakses data pribadi</li>
                        <li>Memperbaiki data yang tidak akurat</li>
                        <li>Menghapus data (hak untuk dilupakan)</li>
                        <li>Menarik persetujuan kapan saja</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
