<?= $this->extend('templates/admin_layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0 text-gray-800"><?= esc($title) ?></h1>
                <a href="<?= site_url('admin/respondents/export') ?>" class="btn btn-success">
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
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter me-2"></i>Filter Responden</h6>
        </div>
        <div class="card-body">
            <form method="get" action="<?= site_url('admin/respondents') ?>" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-select">
                        <option value="">Semua</option>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Pendidikan</label>
                    <select name="pendidikan" class="form-select">
                        <option value="">Semua</option>
                        <option value="SD">SD/Sederajat</option>
                        <option value="SMP">SMP/Sederajat</option>
                        <option value="SMA">SMA/Sederajat</option>
                        <option value="D3">Diploma III</option>
                        <option value="S1">Sarjana (S1)</option>
                        <option value="S2">Magister (S2)</option>
                        <option value="S3">Doktor (S3)</option>
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
                    <a href="<?= site_url('admin/respondents') ?>" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Total Responden</h6>
                            <h2 class="mb-0"><?= count($respondents) ?></h2>
                        </div>
                        <i class="fas fa-users fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Responden Anonim</h6>
                            <h2 class="mb-0">
                                <?php
                                $anonim = 0;
                                foreach ($respondents as $r) {
                                    if (empty($r['nama_lengkap'])) $anonim++;
                                }
                                echo $anonim;
                                ?>
                            </h2>
                        </div>
                        <i class="fas fa-user-secret fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Rata-rata Usia</h6>
                            <h2 class="mb-0">
                                <?php
                                $totalUsia = 0;
                                $countUsia = 0;
                                foreach ($respondents as $r) {
                                    if (!empty($r['usia'])) {
                                        $totalUsia += (int)$r['usia'];
                                        $countUsia++;
                                    }
                                }
                                echo $countUsia > 0 ? round($totalUsia / $countUsia) : '-';
                                ?>
                            </h2>
                        </div>
                        <i class="fas fa-chart-bar fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Respondents Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Responden</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="respondentsTable">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="15%">Nama Lengkap</th>
                            <th width="8%">Usia</th>
                            <th width="10%">Jenis Kelamin</th>
                            <th width="12%">Pendidikan</th>
                            <th width="15%">Pekerjaan</th>
                            <th width="15%">Tanggal Input</th>
                            <th width="10%">Status Data</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($respondents)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted">Belum ada data responden.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($respondents as $index => $respondent): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td>
                                        <?php if (empty($respondent['nama_lengkap'])): ?>
                                            <span class="text-muted fst-italic">Anonim</span>
                                        <?php else: ?>
                                            <?= esc($respondent['nama_lengkap']) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($respondent['usia'] ?? '-') ?></td>
                                    <td>
                                        <?php if ($respondent['jenis_kelamin'] === 'L'): ?>
                                            <span class="badge bg-primary">Laki-laki</span>
                                        <?php elseif ($respondent['jenis_kelamin'] === 'P'): ?>
                                            <span class="badge bg-danger">Perempuan</span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($respondent['pendidikan'] ?? '-') ?></td>
                                    <td><?= esc($respondent['pekerjaan'] ?? '-') ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($respondent['tanggal_input'])) ?></td>
                                    <td>
                                        <?php
                                        $db = \Config\Database::connect();
                                        $responseCount = $db->table('tb_respons_survei')
                                            ->where('id_responden', $respondent['id_responden'])
                                            ->countAllResults();
                                        ?>
                                        <?php if ($responseCount > 0): ?>
                                            <span class="badge bg-success">Ada Respons</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Tanpa Respons</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" 
                                                    class="btn btn-info" 
                                                    onclick="viewRespondent(<?= $respondent['id_responden'] ?>)" 
                                                    title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($responseCount === 0): ?>
                                                <button type="button" 
                                                        class="btn btn-danger" 
                                                        onclick="deleteRespondent(<?= $respondent['id_responden'] ?>, '<?= esc($respondent['nama_lengkap'] ?? 'Anonim') ?>')" 
                                                        title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
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

    <!-- Privacy Notice -->
    <div class="alert alert-warning">
        <strong><i class="fas fa-shield-alt me-1"></i>Penting:</strong>
        Data responden dilindungi sesuai UU PDP (Perlindungan Data Pribadi). 
        Pastikan akses ke data ini terbatas hanya untuk petugas yang berwenang.
        Data sensitif seperti NIK dan email tidak ditampilkan secara penuh.
    </div>
</div>

<!-- Modal Detail Respondent -->
<div class="modal fade" id="respondentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Responden</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="respondentModalBody">
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
function viewRespondent(id) {
    const modal = new bootstrap.Modal(document.getElementById('respondentModal'));
    document.getElementById('respondentModalBody').innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    modal.show();
    
    fetch('<?= site_url('admin/respondents/show/') ?>' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const r = data.data;
                let html = `
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Nama Lengkap:</label>
                            <p>${r.nama_lengkap || '<em>Anonim</em>'}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Usia:</label>
                            <p>${r.usia || '-'}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Jenis Kelamin:</label>
                            <p>${r.jenis_kelamin === 'L' ? 'Laki-laki' : (r.jenis_kelamin === 'P' ? 'Perempuan' : '-')}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Pendidikan:</label>
                            <p>${r.pendidikan || '-'}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Pekerjaan:</label>
                            <p>${r.pekerjaan || '-'}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Tanggal Input:</label>
                            <p>${r.tanggal_input}</p>
                        </div>
                    </div>
                `;
                
                if (r.alamat) {
                    html += `
                        <div class="mb-3">
                            <label class="fw-bold">Alamat:</label>
                            <p>${r.alamat}</p>
                        </div>
                    `;
                }
                
                // Masked sensitive data notice
                html += `
                    <div class="alert alert-info mt-3">
                        <strong><i class="fas fa-lock me-1"></i>Data Sensitif:</strong><br>
                        Data seperti NIK, email, dan telepon disembunyikan untuk melindungi privasi responden 
                        sesuai dengan UU Perlindungan Data Pribadi.
                    </div>
                `;
                
                document.getElementById('respondentModalBody').innerHTML = html;
            } else {
                document.getElementById('respondentModalBody').innerHTML = `
                    <div class="alert alert-danger">${data.message}</div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('respondentModalBody').innerHTML = `
                <div class="alert alert-danger">Terjadi kesalahan saat memuat data.</div>
            `;
        });
}

function deleteRespondent(id, name) {
    if (confirm(`Apakah Anda yakin ingin menghapus responden "${name}"? Tindakan ini tidak dapat dibatalkan.`)) {
        window.location.href = '<?= site_url('admin/respondents/delete/') ?>' + id;
    }
}
</script>

<?= $this->endSection() ?>
