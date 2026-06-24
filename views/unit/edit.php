<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Unit Layanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Edit Unit Layanan</h5>
                </div>
                <div class="card-body">
                    <?php if(isset($_SESSION['flash_error'])): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach($_SESSION['flash_error'] as $err): ?>
                                    <li><?= htmlspecialchars($err) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php unset($_SESSION['flash_error']); ?>
                    <?php endif; ?>

                    <form action="/units/update/<?= $unit['id'] ?>" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Kode Unit</label>
                            <input type="text" name="kode_unit" class="form-control" required value="<?= htmlspecialchars($unit['kode_unit']) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Unit</label>
                            <input type="text" name="nama_unit" class="form-control" required value="<?= htmlspecialchars($unit['nama_unit']) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="3"><?= htmlspecialchars($unit['deskripsi']) ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="2"><?= htmlspecialchars($unit['alamat']) ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kontak</label>
                            <input type="text" name="kontak" class="form-control" value="<?= htmlspecialchars($unit['kontak']) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="aktif" <?= $unit['status'] === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                <option value="nonaktif" <?= $unit['status'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                            </select>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="/units/<?= $unit['id'] ?>" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
