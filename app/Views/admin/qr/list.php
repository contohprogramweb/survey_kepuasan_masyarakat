<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-qrcode"></i> <?= esc($title) ?></h2>
                <a href="<?= site_url('admin/qr') ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Buat QR Code Baru
                </a>
            </div>
        </div>
    </div>
    
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Filter Unit</label>
                    <select name="id_unit" class="form-select">
                        <option value="">Semua Unit</option>
                        <?php foreach ($units as $unit): ?>
                            <option value="<?= $unit['id_unit'] ?>" <?= ($idUnit ?? '') == $unit['id_unit'] ? 'selected' : '' ?>>
                                <?= esc($unit['nama_unit']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="<?= site_url('admin/qr/list') ?>" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Unit Layanan</th>
                            <th>Periode</th>
                            <th>Short URL</th>
                            <th>Format</th>
                            <th>Ukuran</th>
                            <th>Scan Count</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($qrCodes)): ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>Belum ada QR Code yang dibuat.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($qrCodes as $qr): ?>
                                <tr>
                                    <td><?= $qr['id_qr'] ?></td>
                                    <td><?= esc($qr['nama_unit']) ?></td>
                                    <td><?= $qr['nama_periode'] ? esc($qr['nama_periode']) : '<span class="text-muted">-</span>' ?></td>
                                    <td>
                                        <code><?= base_url('q/' . $qr['short_url']) ?></code>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $qr['format'] === 'png' ? 'success' : ($qr['format'] === 'svg' ? 'info' : 'warning') ?>">
                                            <?= strtoupper($qr['format']) ?>
                                        </span>
                                    </td>
                                    <td><?= $qr['size_preset'] ?? '-' ?></td>
                                    <td>
                                        <span class="badge bg-primary"><?= $qr['scan_count'] ?> scan</span>
                                    </td>
                                    <td>
                                        <?php if ($qr['is_active']): ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($qr['created_at'])) ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= site_url('admin/qr/download/' . $qr['id_qr']) ?>" class="btn btn-success" title="Unduh">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <a href="<?= site_url('admin/qr/print/' . $qr['id_qr']) ?>" class="btn btn-info" title="Cetak">
                                                <i class="fas fa-print"></i>
                                            </a>
                                            <button class="btn btn-<?= $qr['is_active'] ? 'warning' : 'success' ?>" 
                                                    onclick="toggleStatus(<?= $qr['id_qr'] ?>)" 
                                                    title="<?= $qr['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>">
                                                <i class="fas fa-<?= $qr['is_active'] ? 'pause' : 'play' ?>"></i>
                                            </button>
                                            <button class="btn btn-danger" onclick="deleteQR(<?= $qr['id_qr'] ?>)" title="Hapus">
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

<?= $this->section('scripts') ?>
<script>
function toggleStatus(idQr) {
    if (!confirm('Apakah Anda yakin ingin mengubah status QR Code ini?')) return;
    
    fetch('<?= site_url('admin/qr/toggle/') ?>' + idQr, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Gagal mengubah status');
        }
    });
}

function deleteQR(idQr) {
    if (!confirm('Apakah Anda yakin ingin menghapus QR Code ini? File akan dihapus permanen.')) return;
    
    fetch('<?= site_url('admin/qr/') ?>' + idQr, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Gagal menghapus QR Code');
        }
    });
}
</script>
<?= $this->endSection() ?>
