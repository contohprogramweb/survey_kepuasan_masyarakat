<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Unit Layanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Tambah Unit Layanan Baru</h5>
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

                    <form action="/units/store" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Kode Unit <span class="text-danger">*</span></label>
                            <input type="text" name="kode_unit" class="form-control" required 
                                   value="<?= htmlspecialchars($_SESSION['old_input']['kode_unit'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Unit <span class="text-danger">*</span></label>
                            <input type="text" name="nama_unit" class="form-control" required
                                   value="<?= htmlspecialchars($_SESSION['old_input']['nama_unit'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="3"><?= htmlspecialchars($_SESSION['old_input']['deskripsi'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="2"><?= htmlspecialchars($_SESSION['old_input']['alamat'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kontak</label>
                            <input type="text" name="kontak" class="form-control" 
                                   value="<?= htmlspecialchars($_SESSION['old_input']['kontak'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="aktif" <?= ($_SESSION['old_input']['status'] ?? '') === 'nonaktif' ? '' : 'selected' ?>>Aktif</option>
                                <option value="nonaktif" <?= ($_SESSION['old_input']['status'] ?? '') === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                            </select>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="/units" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php unset($_SESSION['old_input']); ?>
</body>
</html>
