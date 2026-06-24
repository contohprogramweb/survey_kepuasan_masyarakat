<?= $this->extend('templates/admin_layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0 text-gray-800"><?= esc($title) ?></h1>
                <a href="<?= site_url('admin/responses') ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Respons
                </a>
            </div>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= esc(session()->getFlashdata('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-info text-white shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Total Saran</h6>
                            <h2 class="mb-0"><?= count($saran) ?></h2>
                        </div>
                        <i class="fas fa-lightbulb fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Saran Bulan Ini</h6>
                            <h2 class="mb-0">
                                <?php
                                $bulanIni = 0;
                                foreach ($saran as $s) {
                                    if (date('Y-m', strtotime($s['tanggal_input'])) === date('Y-m')) $bulanIni++;
                                }
                                echo $bulanIni;
                                ?>
                            </h2>
                        </div>
                        <i class="fas fa-calendar fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-white shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Perlu Tindak Lanjut</h6>
                            <h2 class="mb-0">
                                <?php
                                $perluTindakLanjut = 0;
                                foreach ($saran as $s) {
                                    if (empty($s['tindak_lanjut'])) $perluTindakLanjut++;
                                }
                                echo $perluTindakLanjut;
                                ?>
                            </h2>
                        </div>
                        <i class="fas fa-tasks fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Saran Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Saran/Masukan Masyarakat</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="saranTable">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="15%">Tanggal</th>
                            <th width="20%">Unit Layanan</th>
                            <th width="35%">Saran/Masukan</th>
                            <th width="15%">Tindak Lanjut</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($saran)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted">Belum ada saran/masukan.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($saran as $index => $item): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($item['tanggal_input'])) ?></td>
                                    <td><?= esc($item['nama_unit'] ?? '-') ?></td>
                                    <td>
                                        <span class="text-dark"><?= esc(substr($item['saran'], 0, 100)) ?><?= strlen($item['saran']) > 100 ? '...' : '' ?></span>
                                    </td>
                                    <td>
                                        <?php if (!empty($item['tindak_lanjut'])): ?>
                                            <span class="badge bg-success">Ditindaklanjuti</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Belum</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" 
                                                class="btn btn-sm btn-info" 
                                                onclick="viewSaran(<?= $item['id_saran'] ?>)" 
                                                title="Lihat Detail">
                                            <i class="fas fa-eye"></i> Lihat
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Saran -->
<div class="modal fade" id="saranModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Saran/Masukan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="saranModalBody">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewSaran(id) {
    const modal = new bootstrap.Modal(document.getElementById('saranModal'));
    document.getElementById('saranModalBody').innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    modal.show();
    
    // Fetch detail from response API
    fetch('<?= site_url('admin/responses/show/') ?>' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.saran) {
                const s = data.data;
                let html = `
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="fw-bold">Tanggal:</label>
                            <p>${s.tanggal_input}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold">Unit Layanan:</label>
                            <p>${s.nama_unit || '-'}</p>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="fw-bold">Saran/Masukan:</label>
                        <div class="alert alert-light border">
                            ${s.saran.saran || 'Tidak ada saran'}
                        </div>
                    </div>
                `;
                
                if (s.saran.tindak_lanjut) {
                    html += `
                        <div class="mb-3">
                            <label class="fw-bold">Tindak Lanjut:</label>
                            <div class="alert alert-success">
                                ${s.saran.tindak_lanjut}
                            </div>
                        </div>
                    `;
                } else {
                    html += `
                        <div class="mb-3">
                            <form action="<?= site_url('admin/saran/update') ?>" method="post">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id_saran" value="${s.saran.id_saran}">
                                <label class="fw-bold">Tambah Tindak Lanjut:</label>
                                <textarea class="form-control" name="tindak_lanjut" rows="3" placeholder="Catat tindak lanjut untuk saran ini..."></textarea>
                                <button type="submit" class="btn btn-primary mt-2">
                                    <i class="fas fa-save me-1"></i> Simpan Tindak Lanjut
                                </button>
                            </form>
                        </div>
                    `;
                }
                
                document.getElementById('saranModalBody').innerHTML = html;
            } else {
                document.getElementById('saranModalBody').innerHTML = `
                    <div class="alert alert-danger">Data tidak ditemukan</div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('saranModalBody').innerHTML = `
                <div class="alert alert-danger">Terjadi kesalahan saat memuat data.</div>
            `;
        });
}
</script>

<?= $this->endSection() ?>
