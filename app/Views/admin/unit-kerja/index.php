<?= $this->extend('templates/admin_layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3"><?= esc($title) ?></h1>
                <a href="<?= site_url('admin/unit-kerja/new') ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Unit Baru
                </a>
            </div>
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
                <table class="table table-hover table-bordered" id="unitsTable">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="10%">Kode Unit</th>
                            <th width="25%">Nama Unit</th>
                            <th width="15%">Instansi</th>
                            <th width="15%">Jenis</th>
                            <th width="15%">Kontak</th>
                            <th width="7%">Status</th>
                            <th width="8%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($units)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted">Belum ada unit kerja. Klik tombol "Tambah Unit Baru" untuk membuat.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($units as $index => $unit): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><code><?= esc($unit['kode_unit']) ?></code></td>
                                    <td><?= esc($unit['nama_unit']) ?></td>
                                    <td><?= esc($unit['nama_instansi'] ?? '-') ?></td>
                                    <td>
                                        <?php
                                        $jenisLabels = [
                                            'pusat' => 'Pusat',
                                            'cabang' => 'Cabang',
                                            'pembantu' => 'Pembantu'
                                        ];
                                        ?>
                                        <span class="badge bg-primary"><?= esc($jenisLabels[$unit['jenis_unit']] ?? $unit['jenis_unit']) ?></span>
                                    </td>
                                    <td>
                                        <small>
                                            <?php if (!empty($unit['email'])): ?>
                                                <i class="fas fa-envelope"></i> <?= esc(substr($unit['email'], 0, 20)) ?><?= strlen($unit['email']) > 20 ? '...' : '' ?><br>
                                            <?php endif; ?>
                                            <?php if (!empty($unit['telepon'])): ?>
                                                <i class="fas fa-phone"></i> <?= esc($unit['telepon']) ?>
                                            <?php endif; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($unit['is_active']): ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= site_url('admin/unit-kerja/' . $unit['id_unit'] . '/edit') ?>" class="btn btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-info" onclick="toggleStatus(<?= $unit['id_unit'] ?>)" title="Toggle Status">
                                                <i class="fas fa-sync"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger" onclick="deleteUnit(<?= $unit['id_unit'] ?>)" title="Hapus">
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

<script>
function toggleStatus(id) {
    if (!confirm('Apakah Anda yakin ingin mengubah status unit ini?')) return;
    
    fetch('<?= site_url('admin/unit-kerja') ?>/' + id + '/toggle-status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message);
        }
    });
}

function deleteUnit(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus unit ini? Tindakan ini tidak dapat dibatalkan.')) return;
    
    fetch('<?= site_url('admin/unit-kerja') ?>/' + id, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (response.ok) {
            location.reload();
        } else {
            return response.json().then(err => { throw err; });
        }
    })
    .catch(error => {
        alert('Gagal menghapus unit: ' + (error.message || 'Terjadi kesalahan'));
    });
}
</script>
<?= $this->endSection() ?>
