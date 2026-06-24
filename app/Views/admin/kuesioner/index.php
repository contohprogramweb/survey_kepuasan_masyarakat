<?= $this->extend('templates/admin_layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0 text-gray-800"><?= esc($title) ?></h1>
                <a href="<?= site_url('admin/kuesioner/edit/1') ?>" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Tambah Unsur
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

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Unsur Kuesioner IKM</h6>
            <a href="<?= site_url('admin/kuesioner/preview') ?>" class="btn btn-sm btn-outline-info" target="_blank">
                <i class="fas fa-eye me-1"></i> Preview
            </a>
        </div>
        <div class="card-body">
            <div class="alert alert-info mb-4">
                <strong><i class="fas fa-info-circle me-1"></i>Informasi:</strong>
                Kuesioner IKM terdiri dari 9 unsur wajib sesuai PermenPANRB No. 14 Tahun 2017. 
                Setiap unsur memiliki bobot equal (0.111) dan skala penilaian 1-4.
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="kuesionerTable">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="10%">Kode</th>
                            <th width="30%">Nama Unsur</th>
                            <th width="25%">Deskripsi</th>
                            <th width="8%">Bobot</th>
                            <th width="7%">Urutan</th>
                            <th width="8%">Status</th>
                            <th width="7%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($elements)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted">Belum ada unsur kuesioner.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($elements as $index => $element): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><code><?= esc($element['kode_unsur']) ?></code></td>
                                    <td><strong><?= esc($element['nama_unsur']) ?></strong></td>
                                    <td class="small"><?= esc($element['deskripsi'] ?? '-') ?></td>
                                    <td><?= number_format($element['bobot'] ?? 0.111, 3) ?></td>
                                    <td><?= $element['urutan'] ?></td>
                                    <td>
                                        <?php if ($element['status'] === 'aktif'): ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= site_url('admin/kuesioner/edit/' . $element['id_kuesioner']) ?>" 
                                               class="btn btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-info" 
                                                    onclick="viewElement(<?= $element['id_kuesioner'] ?>)" 
                                                    title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
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

<!-- Modal Detail Element -->
<div class="modal fade" id="elementModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Unsur Kuesioner</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="elementModalBody">
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
function viewElement(id) {
    const modal = new bootstrap.Modal(document.getElementById('elementModal'));
    document.getElementById('elementModalBody').innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    modal.show();
    
    fetch('<?= site_url('admin/kuesioner/show/') ?>' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const el = data.data;
                let html = `
                    <div class="mb-3">
                        <label class="fw-bold">Kode Unsur:</label>
                        <p>${el.kode_unsur}</p>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Nama Unsur:</label>
                        <p>${el.nama_unsur}</p>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Deskripsi:</label>
                        <p>${el.deskripsi || '-'}</p>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <label class="fw-bold">Bobot:</label>
                            <p>${parseFloat(el.bobot).toFixed(3)}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="fw-bold">Urutan:</label>
                            <p>${el.urutan}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="fw-bold">Status:</label>
                            <p><span class="badge bg-${el.status === 'aktif' ? 'success' : 'secondary'}">${el.status === 'aktif' ? 'Aktif' : 'Nonaktif'}</span></p>
                        </div>
                    </div>
                `;
                
                if (el.questions && el.questions.length > 0) {
                    html += `
                        <hr>
                        <h6>Pertanyaan Terkait (${el.questions.length}):</h6>
                        <ul class="list-group">
                    `;
                    el.questions.forEach((q, idx) => {
                        html += `
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${idx + 1}.</strong> ${q.pertanyaan}
                                    <br><small class="text-muted">Tipe: ${q.tipe_input}</small>
                                </div>
                                <span class="badge bg-primary rounded-pill">${q.tipe_input}</span>
                            </li>
                        `;
                    });
                    html += '</ul>';
                } else {
                    html += `<p class="text-muted mt-3">Belum ada pertanyaan untuk unsur ini.</p>`;
                }
                
                document.getElementById('elementModalBody').innerHTML = html;
            } else {
                document.getElementById('elementModalBody').innerHTML = `
                    <div class="alert alert-danger">${data.message}</div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('elementModalBody').innerHTML = `
                <div class="alert alert-danger">Terjadi kesalahan saat memuat data.</div>
            `;
        });
}
</script>

<?= $this->endSection() ?>
