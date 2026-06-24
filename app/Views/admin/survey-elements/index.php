<?= $this->extend('templates/admin_layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3"><?= esc($title) ?></h1>
                <a href="<?= site_url('admin/survey-elements/new') ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Unsur Baru
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
                <table class="table table-hover table-bordered" id="elementsTable">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="10%">Kode</th>
                            <th width="25%">Nama Unsur</th>
                            <th width="25%">Deskripsi</th>
                            <th width="10%">Bobot</th>
                            <th width="8%">Urutan</th>
                            <th width="7%">Status</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($elements)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted">Belum ada unsur survei. Klik tombol "Tambah Unsur Baru" untuk membuat.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($elements as $index => $element): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><code><?= esc($element['kode_unsur']) ?></code></td>
                                    <td><?= esc($element['nama_unsur']) ?></td>
                                    <td><?= esc(substr($element['deskripsi'] ?? '', 0, 50)) ?><?= strlen($element['deskripsi'] ?? '') > 50 ? '...' : '' ?></td>
                                    <td><?= esc($element['bobot']) ?></td>
                                    <td><?= esc($element['urutan']) ?></td>
                                    <td>
                                        <?php if ($element['is_active']): ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= site_url('admin/survey-elements/' . $element['id_kuesioner'] . '/edit') ?>" class="btn btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-info" onclick="toggleStatus(<?= $element['id_kuesioner'] ?>)" title="Toggle Status">
                                                <i class="fas fa-sync"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger" onclick="deleteElement(<?= $element['id_kuesioner'] ?>)" title="Hapus">
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
    if (!confirm('Apakah Anda yakin ingin mengubah status unsur ini?')) return;
    
    fetch('<?= site_url('admin/survey-elements') ?>/' + id + '/toggle-status', {
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

function deleteElement(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus unsur ini? Tindakan ini tidak dapat dibatalkan.')) return;
    
    fetch('<?= site_url('admin/survey-elements') ?>/' + id, {
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
        alert('Gagal menghapus unsur: ' + (error.message || 'Terjadi kesalahan'));
    });
}
</script>
<?= $this->endSection() ?>
