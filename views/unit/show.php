<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Unit Layanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm mb-3">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-building fa-4x text-primary"></i>
                    </div>
                    <h4><?= htmlspecialchars($unit['nama_unit']) ?></h4>
                    <span class="badge <?= $unit['status'] === 'aktif' ? 'bg-success' : 'bg-secondary' ?>">
                        <?= ucfirst($unit['status']) ?>
                    </span>
                    <div class="mt-3">
                        <?php if(in_array($_SESSION['user']['role'], ['Super Admin', 'Admin'])): ?>
                            <a href="/units/<?= $unit['id'] ?>/edit" class="btn btn-warning btn-sm text-white">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        <?php endif; ?>
                        <a href="/units" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Informasi Detail</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="200">Kode Unit</th>
                            <td><?= htmlspecialchars($unit['kode_unit']) ?></td>
                        </tr>
                        <tr>
                            <th>Nama Unit</th>
                            <td><?= htmlspecialchars($unit['nama_unit']) ?></td>
                        </tr>
                        <tr>
                            <th>Deskripsi</th>
                            <td><?= nl2br(htmlspecialchars($unit['deskripsi'] ?? '-')) ?></td>
                        </tr>
                        <tr>
                            <th>Alamat</th>
                            <td><?= nl2br(htmlspecialchars($unit['alamat'] ?? '-')) ?></td>
                        </tr>
                        <tr>
                            <th>Kontak</th>
                            <td><?= htmlspecialchars($unit['kontak'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <th>Dibuat Pada</th>
                            <td><?= date('d M Y H:i', strtotime($unit['created_at'])) ?></td>
                        </tr>
                        <tr>
                            <th>Terakhir Diupdate</th>
                            <td><?= date('d M Y H:i', strtotime($unit['updated_at'])) ?></td>
                        </tr>
                    </table>

                    <?php if($hasRelations): ?>
                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Peringatan:</strong> Unit ini memiliki data terkait (periode) dan tidak dapat dihapus.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Audit Log Section (Placeholder) -->
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Riwayat Aktivitas</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Fitur audit log akan menampilkan riwayat perubahan unit ini.</p>
                    <!-- Loop audit logs here -->
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
