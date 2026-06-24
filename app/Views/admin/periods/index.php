<?= $this->extend('templates/admin_layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0 text-gray-800"><?= esc($title) ?></h1>
                <a href="<?= site_url('admin/periods/new') ?>" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Tambah Periode Baru
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

    <?php if ($activePeriod): ?>
        <div class="alert alert-info mb-4">
            <strong><i class="fas fa-info-circle me-1"></i> Periode Aktif:</strong>
            <?= esc($activePeriod['nama_periode']) ?>
            (<?= date('d M Y', strtotime($activePeriod['tanggal_mulai'])) ?> - <?= date('d M Y', strtotime($activePeriod['tanggal_selesai'])) ?>)
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Periode Survei</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="periodsTable">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="20%">Nama Periode</th>
                            <th width="15%">Tanggal Mulai</th>
                            <th width="15%">Tanggal Selesai</th>
                            <th width="25%">Deskripsi</th>
                            <th width="10%">Status</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($periods)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted">Belum ada periode survei. Klik tombol "Tambah Periode Baru" untuk membuat periode pertama.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($periods as $index => $periode): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= esc($periode['nama_periode']) ?></td>
                                    <td><?= date('d M Y', strtotime($periode['tanggal_mulai'])) ?></td>
                                    <td><?= date('d M Y', strtotime($periode['tanggal_selesai'])) ?></td>
                                    <td><?= esc($periode['deskripsi'] ?? '-') ?></td>
                                    <td>
                                        <?php if ($periode['status'] === 'aktif'): ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= site_url('admin/periods/edit/' . $periode['id_periode']) ?>" 
                                               class="btn btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-danger" 
                                                    onclick="deletePeriod(<?= $periode['id_periode'] ?>, '<?= esc($periode['nama_periode']) ?>')"
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

<script>
function deletePeriod(id, name) {
    if (confirm(`Apakah Anda yakin ingin menghapus periode "${name}"? Tindakan ini tidak dapat dibatalkan.`)) {
        window.location.href = '<?= site_url('admin/periods/delete/') ?>' + id;
    }
}
</script>

<?= $this->endSection() ?>
