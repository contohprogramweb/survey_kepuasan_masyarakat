<?= $this->extend('templates/admin_layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0 text-gray-800"><?= esc($title) ?></h1>
                <a href="<?= site_url('admin/responses/export/1') ?>" class="btn btn-success">
                    <i class="fas fa-file-export me-1"></i> Export CSV
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

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= esc(session()->getFlashdata('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filter Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter me-2"></i>Filter Respons</h6>
        </div>
        <div class="card-body">
            <form method="get" action="<?= site_url('admin/responses') ?>" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Periode</label>
                    <select name="periode" class="form-select">
                        <option value="">Semua Periode</option>
                        <!-- Populate from database -->
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Unit Layanan</label>
                    <select name="unit" class="form-select">
                        <option value="">Semua Unit</option>
                        <!-- Populate from database -->
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Akhir</label>
                    <input type="date" name="tanggal_akhir" class="form-control">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                    <a href="<?= site_url('admin/responses') ?>" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Total Respons</h6>
                            <h2 class="mb-0">1,234</h2>
                        </div>
                        <i class="fas fa-comments fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Rata-rata IKM</h6>
                            <h2 class="mb-0">3.45</h2>
                        </div>
                        <i class="fas fa-chart-line fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Bulan Ini</h6>
                            <h2 class="mb-0">156</h2>
                        </div>
                        <i class="fas fa-calendar fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Dengan Saran</h6>
                            <h2 class="mb-0">89</h2>
                        </div>
                        <i class="fas fa-lightbulb fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Responses Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Respons Survei</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="responsesTable">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="12%">Tanggal Input</th>
                            <th width="20%">Unit Layanan</th>
                            <th width="15%">Periode</th>
                            <th width="10%">Nilai IKM</th>
                            <th width="8%">Jawaban</th>
                            <th width="10%">Status</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($responses)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted">Belum ada respons survei.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($responses as $index => $response): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($response['tanggal_input'])) ?></td>
                                    <td><?= esc($response['nama_unit'] ?? '-') ?></td>
                                    <td><?= esc($response['nama_periode'] ?? '-') ?></td>
                                    <td>
                                        <?php if (isset($response['nilai_ikm'])): ?>
                                            <strong><?= number_format($response['nilai_ikm'], 2) ?></strong>
                                            <?php
                                            $kategori = '';
                                            $badge = '';
                                            if ($response['nilai_ikm'] >= 3.51) {
                                                $kategori = 'Sangat Baik';
                                                $badge = 'bg-success';
                                            } elseif ($response['nilai_ikm'] >= 2.51) {
                                                $kategori = 'Baik';
                                                $badge = 'bg-info';
                                            } elseif ($response['nilai_ikm'] >= 1.51) {
                                                $kategori = 'Kurang Baik';
                                                $badge = 'bg-warning';
                                            } else {
                                                $kategori = 'Sangat Kurang';
                                                $badge = 'bg-danger';
                                            }
                                            ?>
                                            <br><span class="badge <?= $badge ?>"><?= $kategori ?></span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $response['total_jawaban'] ?? 0 ?></td>
                                    <td>
                                        <span class="badge bg-success">Valid</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" 
                                                    class="btn btn-info" 
                                                    onclick="viewResponse(<?= $response['id_respons'] ?>)" 
                                                    title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-danger" 
                                                    onclick="deleteResponse(<?= $response['id_respons'] ?>)" 
                                                    title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
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

<!-- Modal Detail Response -->
<div class="modal fade" id="responseModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Respons Survei</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="responseModalBody">
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
function viewResponse(id) {
    const modal = new bootstrap.Modal(document.getElementById('responseModal'));
    document.getElementById('responseModalBody').innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    modal.show();
    
    fetch('<?= site_url('admin/responses/show/') ?>' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const r = data.data;
                let html = `
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Tanggal:</strong><br>${r.tanggal_input}
                        </div>
                        <div class="col-md-6">
                            <strong>Unit Layanan:</strong><br>${r.nama_unit || '-'}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Periode:</strong><br>${r.nama_periode || '-'}
                        </div>
                        <div class="col-md-6">
                            <strong>Nilai IKM:</strong><br>
                            <span class="badge bg-success">${parseFloat(r.nilai_ikm).toFixed(2)}</span>
                        </div>
                    </div>
                    <hr>
                    <h6>Jawaban Responden:</h6>
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Kode Unsur</th>
                                <th>Jawaban</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                
                if (r.answers && r.answers.length > 0) {
                    r.answers.forEach((a, idx) => {
                        let nilaiText = '';
                        switch(parseInt(a.nilai_jawaban)) {
                            case 1: nilaiText = 'Sangat Tidak Puas'; break;
                            case 2: nilaiText = 'Tidak Puas'; break;
                            case 3: nilaiText = 'Puas'; break;
                            case 4: nilaiText = 'Sangat Puas'; break;
                        }
                        html += `
                            <tr>
                                <td>${idx + 1}</td>
                                <td>${a.kode_unsur || '-'}</td>
                                <td><span class="badge bg-primary">${nilaiText}</span></td>
                            </tr>
                        `;
                    });
                } else {
                    html += `<tr><td colspan="3" class="text-center">Tidak ada jawaban</td></tr>`;
                }
                
                html += `</tbody></table>`;
                
                if (r.saran) {
                    html += `
                        <div class="alert alert-info">
                            <strong>Saran/Masukan:</strong><br>
                            ${r.saran.saran || 'Tidak ada saran'}
                        </div>
                    `;
                }
                
                document.getElementById('responseModalBody').innerHTML = html;
            } else {
                document.getElementById('responseModalBody').innerHTML = `
                    <div class="alert alert-danger">${data.message}</div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('responseModalBody').innerHTML = `
                <div class="alert alert-danger">Terjadi kesalahan saat memuat data.</div>
            `;
        });
}

function deleteResponse(id) {
    if (confirm('Apakah Anda yakin ingin menghapus respons ini? Tindakan ini tidak dapat dibatalkan.')) {
        window.location.href = '<?= site_url('admin/responses/delete/') ?>' + id;
    }
}
</script>

<?= $this->endSection() ?>
