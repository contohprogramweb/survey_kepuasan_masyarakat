<?= $this->extend('templates/admin_layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h4><i class="fas fa-database"></i> <?= esc($title) ?></h4>
            <form action="<?= site_url('admin/backup/create') ?>" method="post" style="display:inline;">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Buat Backup Baru
                </button>
            </form>
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

    <div class="card shadow">
        <div class="card-header bg-info text-white">
            <h6 class="mb-0"><i class="fas fa-history"></i> Daftar Backup Tersedia</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="backupsTable">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nama File</th>
                            <th>Ukuran</th>
                            <th>Tanggal Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($backups as $backup): ?>
                        <tr>
                            <td><?= esc($backup['id']) ?></td>
                            <td><code><?= esc($backup['filename']) ?></code></td>
                            <td><?= esc($backup['size']) ?></td>
                            <td><?= esc($backup['created_at']) ?></td>
                            <td>
                                <form action="<?= site_url('admin/backup/restore') ?>" method="post" style="display:inline;" onsubmit="return confirm('Restore dari backup ini? Sistem akan restart setelah restore.')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="backup_file" value="<?= esc($backup['filename']) ?>">
                                    <button type="submit" class="btn btn-warning btn-sm" title="Restore">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                </form>
                                <form action="<?= site_url('admin/backup/' . $backup['id']) ?>" method="post" style="display:inline;" onsubmit="return confirm('Hapus backup ini?')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                <a href="#" class="btn btn-secondary btn-sm" title="Download">
                                    <i class="fas fa-download"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Informasi Backup</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li><strong>Lokasi Backup:</strong> /app/Database/Backups/</li>
                        <li><strong>Format:</strong> SQL Dump</li>
                        <li><strong>Retensi:</strong> 7 backup terakhir</li>
                        <li><strong>Jadwal Otomatis:</strong> Setiap hari pukul 02:00 WIB</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Perhatian</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li>• Restore akan menghentikan aplikasi sementara</li>
                        <li>• Pastikan tidak ada user yang sedang aktif</li>
                        <li>• Backup data penting sebelum restore</li>
                        <li>• Hanya admin yang bisa melakukan restore</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
