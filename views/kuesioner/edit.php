<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Kuesioner IKM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i>Edit Kuesioner
                        <?php if($isUnsurWajib): ?>
                            <span class="badge bg-danger ms-2">Unsur Wajib (Tidak bisa dihapus)</span>
                        <?php endif; ?>
                    </h5>
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

                    <?php if(isset($_SESSION['flash_success'])): ?>
                        <div class="alert alert-success">
                            <?= htmlspecialchars($_SESSION['flash_success']) ?>
                        </div>
                        <?php unset($_SESSION['flash_success']); ?>
                    <?php endif; ?>

                    <!-- Info Box for Wajib Unsur -->
                    <?php if($isUnsurWajib): ?>
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Unsur Wajib IKM:</strong> Ini adalah unsur wajib (<?= htmlspecialchars($kuesioner['unsur_code']) ?>). 
                            Kode unsur tidak dapat diubah, namun Anda dapat menyesuaikan teks pertanyaan dan deskripsi untuk penyesuaian bahasa.
                        </div>
                    <?php endif; ?>

                    <form action="/kuesioner/update/<?= $kuesioner['id_kuesioner'] ?>" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Kode Unsur</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($kuesioner['unsur_code']) ?>" disabled>
                            <small class="text-muted">Kode unsur tidak dapat diubah</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Nama Unsur / Pertanyaan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_unsur" class="form-control" required 
                                   value="<?= htmlspecialchars($kuesioner['nama_unsur']) ?>"
                                   placeholder="Contoh: Kualitas Persyaratan Pelayanan">
                            <small class="text-muted">Teks ini akan ditampilkan sebagai judul pertanyaan di kuesioner</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Deskripsi / Petunjuk</label>
                            <textarea name="deskripsi" class="form-control" rows="3" 
                                      placeholder="Berikan penjelasan atau petunjuk tambahan untuk responden"><?= htmlspecialchars($kuesioner['deskripsi']) ?></textarea>
                            <small class="text-muted">Deskripsi akan ditampilkan di bawah pertanyaan untuk memberikan konteks</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Bobot Nilai</label>
                            <input type="number" name="bobot" class="form-control" step="0.01" min="0.01" max="10"
                                   value="<?= htmlspecialchars($kuesioner['bobot']) ?>">
                            <small class="text-muted">Bobot digunakan dalam perhitungan nilai IKM (default: 1.00)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Urutan Tampil</label>
                            <input type="number" name="urutan" class="form-control" min="1" 
                                   value="<?= htmlspecialchars($kuesioner['urutan']) ?>">
                            <small class="text-muted">Urutan pertanyaan ditampilkan kepada responden</small>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" id="isActive" 
                                   <?= $kuesioner['is_active'] == 1 ? 'checked' : '' ?>>
                            <label class="form-check-label" for="isActive">Aktif</label>
                            <small class="text-muted d-block">Jika dicentang, pertanyaan ini akan ditampilkan kepada responden</small>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2">
                            <a href="/kuesioner/<?= $kuesioner['id_kuesioner'] ?>" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Back Link -->
            <div class="mt-3">
                <a href="/kuesioner" class="text-decoration-none">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar Kuesioner
                </a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
