<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Kuesioner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-clipboard-list me-2"></i>Detail Kuesioner
                        <?php if($isUnsurWajib): ?>
                            <span class="badge bg-warning text-dark ms-2">Unsur Wajib</span>
                        <?php endif; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if(isset($_SESSION['flash_success'])): ?>
                        <div class="alert alert-success">
                            <?= htmlspecialchars($_SESSION['flash_success']) ?>
                        </div>
                        <?php unset($_SESSION['flash_success']); ?>
                    <?php endif; ?>

                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Kode Unsur</th>
                            <td><?= htmlspecialchars($kuesioner['unsur_code']) ?></td>
                        </tr>
                        <tr>
                            <th>Nama Unsur / Pertanyaan</th>
                            <td><?= htmlspecialchars($kuesioner['nama_unsur']) ?></td>
                        </tr>
                        <tr>
                            <th>Deskripsi</th>
                            <td><?= nl2br(htmlspecialchars($kuesioner['deskripsi'] ?? '-')) ?></td>
                        </tr>
                        <tr>
                            <th>Bobot</th>
                            <td><?= number_format($kuesioner['bobot'], 2) ?></td>
                        </tr>
                        <tr>
                            <th>Urutan</th>
                            <td><?= $kuesioner['urutan'] ?></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <?php if($kuesioner['is_active'] == 1): ?>
                                    <span class="badge bg-success">Aktif</span>
                                    <small class="text-muted d-block mt-1">Ditampilkan kepada responden</small>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Nonaktif</span>
                                    <small class="text-muted d-block mt-1">Tidak ditampilkan kepada responden</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Dibuat</th>
                            <td><?= date('d/m/Y H:i', strtotime($kuesioner['created_at'])) ?></td>
                        </tr>
                        <tr>
                            <th>Terakhir Diupdate</th>
                            <td><?= date('d/m/Y H:i', strtotime($kuesioner['updated_at'])) ?></td>
                        </tr>
                    </table>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="/kuesioner" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                        <?php if(in_array($_SESSION['user']['role'], ['Super Admin', 'Admin'])): ?>
                            <a href="/kuesioner/<?= $kuesioner['id_kuesioner'] ?>/edit" class="btn btn-warning text-white">
                                <i class="fas fa-edit me-1"></i> Edit
                            </a>
                            <button onclick="toggleStatus()" class="btn btn-<?= $kuesioner['is_active'] == 1 ? 'secondary' : 'success' ?>">
                                <i class="fas fa-<?= $kuesioner['is_active'] == 1 ? 'pause' : 'play' ?> me-1"></i> 
                                <?= $kuesioner['is_active'] == 1 ? 'Nonaktifkan' : 'Aktifkan' ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Back Link -->
            <div class="mt-3">
                <a href="/kuesioner" class="text-decoration-none">
                    <i class="fas fa-list me-1"></i> Lihat Semua Kuesioner
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function toggleStatus() {
    Swal.fire({
        title: 'Ubah Status?',
        text: 'Apakah Anda yakin ingin mengubah status kuesioner ini?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Lanjutkan!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '/kuesioner/<?= $kuesioner['id_kuesioner'] ?>/toggle-status';
        }
    });
}
</script>
</body>
</html>
