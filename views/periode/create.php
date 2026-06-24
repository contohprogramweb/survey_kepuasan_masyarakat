<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Periode</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-success text-white">Tambah Periode Survei</div>
                <div class="card-body">
                    <?php if(isset($_SESSION['flash_error'])): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0"><?php foreach($_SESSION['flash_error'] as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
                        </div>
                        <?php unset($_SESSION['flash_error']); ?>
                    <?php endif; ?>

                    <form action="/periods/store" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Unit Layanan</label>
                            <select name="unit_id" class="form-select" required>
                                <option value="">Pilih Unit</option>
                                <?php foreach($units as $id => $name): ?>
                                    <option value="<?= $id ?>" <?= ($_SESSION['old_input']['unit_id'] ?? '') == $id ? 'selected' : '' ?>><?= htmlspecialchars($name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Periode</label>
                            <input type="text" name="nama_periode" class="form-control" required value="<?= htmlspecialchars($_SESSION['old_input']['nama_periode'] ?? '') ?>">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" name="tanggal_mulai" class="form-control" required value="<?= htmlspecialchars($_SESSION['old_input']['tanggal_mulai'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="date" name="tanggal_selesai" class="form-control" required value="<?= htmlspecialchars($_SESSION['old_input']['tanggal_selesai'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="alert alert-info small">
                            <i class="fas fa-info-circle"></i> Status akan diatur otomatis: <b>Draft</b> saat dibuat, <b>Aktif</b> saat tanggal mulai tiba, <b>Selesai</b> setelah tanggal selesai.
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="/periods" class="btn btn-secondary">Batal</a>
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
