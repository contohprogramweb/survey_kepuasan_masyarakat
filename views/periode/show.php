<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Periode</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">Detail Periode</div>
        <div class="card-body">
            <table class="table table-bordered">
                <tr><th width="200">Unit Layanan</th><td><?= htmlspecialchars($periode['unit_nama']) ?></td></tr>
                <tr><th>Nama Periode</th><td><?= htmlspecialchars($periode['nama_periode']) ?></td></tr>
                <tr><th>Tanggal Mulai</th><td><?= date('d M Y', strtotime($periode['tanggal_mulai'])) ?></td></tr>
                <tr><th>Tanggal Selesai</th><td><?= date('d M Y', strtotime($periode['tanggal_selesai'])) ?></td></tr>
                <tr>
                    <th>Status</th>
                    <td>
                        <span class="badge <?= $periode['status'] == 'aktif' ? 'bg-success' : 'bg-secondary' ?>">
                            <?= strtoupper($periode['status']) ?>
                        </span>
                        <?php if($hasData): ?>
                            <span class="badge bg-info text-dark ms-2"><i class="fas fa-database"></i> Ada Data Survei</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
            <a href="/periods" class="btn btn-secondary">Kembali</a>
        </div>
    </div>
</div>
</body>
</html>
