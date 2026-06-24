<?= $this->extend('templates/admin_layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0 text-gray-800"><?= esc($title) ?></h1>
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fas fa-print me-1"></i> Cetak Preview
                </button>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4 no-print">
        <div class="card-header py-3 bg-info text-white">
            <h6 class="m-0"><i class="fas fa-info-circle me-2"></i>Mode Preview</h6>
        </div>
        <div class="card-body">
            <p class="mb-0">Ini adalah preview kuesioner IKM yang akan ditampilkan kepada responden. 
            Untuk mengubah unsur atau pertanyaan, silakan kembali ke halaman manajemen.</p>
        </div>
    </div>

    <!-- Kuesioner Header -->
    <div class="card shadow mb-4" id="kuesionerPreview">
        <div class="card-header text-center">
            <h4><strong>KUESIONER SURVEI KEPUASAN MASYARAKAT</strong></h4>
            <p class="mb-0">Badan Penyelenggara Pelayanan Publik</p>
        </div>
        <div class="card-body">
            <div class="alert alert-light border">
                <h6 class="fw-bold">Petunjuk Pengisian:</h6>
                <ol class="mb-0">
                    <li>Isilah data diri Anda (opsional)</li>
                    <li>Berilah tanda centang (✓) pada kolom yang sesuai dengan penilaian Anda</li>
                    <li>Skala penilaian: 1 = Sangat Tidak Puas, 2 = Tidak Puas, 3 = Puas, 4 = Sangat Puas</li>
                    <li>Semua pertanyaan wajib diisi</li>
                </ol>
            </div>

            <form>
                <!-- Data Responden (Opsional) -->
                <div class="mb-4">
                    <h6 class="fw-bold border-bottom pb-2">A. DATA RESPONDEN (Opsional)</h6>
                    <div class="row mt-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" placeholder="Tidak wajib diisi">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Usia</label>
                            <input type="number" class="form-control" placeholder="Contoh: 30">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jenis Kelamin</label>
                            <select class="form-select">
                                <option value="">Pilih...</option>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pendidikan Terakhir</label>
                            <select class="form-select">
                                <option value="">Pilih...</option>
                                <option value="SD">SD/Sederajat</option>
                                <option value="SMP">SMP/Sederajat</option>
                                <option value="SMA">SMA/Sederajat</option>
                                <option value="D3">Diploma III</option>
                                <option value="S1">Sarjana (S1)</option>
                                <option value="S2">Magister (S2)</option>
                                <option value="S3">Doktor (S3)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Unsur Penilaian -->
                <div class="mb-4">
                    <h6 class="fw-bold border-bottom pb-2">B. PENILAIAN UNSUR PELAYANAN</h6>
                    
                    <?php if (!empty($elements)): ?>
                        <?php foreach ($elements as $element): ?>
                            <div class="card mb-3 border-left-primary">
                                <div class="card-header bg-light">
                                    <strong><?= esc($element['kode_unsur']) ?>. <?= esc($element['nama_unsur']) ?></strong>
                                    <?php if ($element['deskripsi']): ?>
                                        <small class="text-muted d-block"><?= esc($element['deskripsi']) ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <?php
                                    $surveiModel = new \App\Models\SurveiModel();
                                    $questions = $surveiModel->getQuestionsByElement($element['id_kuesioner']);
                                    ?>
                                    
                                    <?php if (!empty($questions)): ?>
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th width="5%">No</th>
                                                    <th width="50%">Pertanyaan</th>
                                                    <th width="45%" class="text-center">Tingkat Kepuasan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($questions as $idx => $question): ?>
                                                    <tr>
                                                        <td><?= $idx + 1 ?></td>
                                                        <td><?= esc($question['pertanyaan']) ?></td>
                                                        <td>
                                                            <div class="d-flex justify-content-center gap-3">
                                                                <div class="form-check text-center">
                                                                    <input class="form-check-input" type="radio" name="q_<?= $question['id_pertanyaan'] ?>" value="1">
                                                                    <label class="form-check-label d-block small">1<br>Sangat<br>Tidak Puas</label>
                                                                </div>
                                                                <div class="form-check text-center">
                                                                    <input class="form-check-input" type="radio" name="q_<?= $question['id_pertanyaan'] ?>" value="2">
                                                                    <label class="form-check-label d-block small">2<br>Tidak<br>Puas</label>
                                                                </div>
                                                                <div class="form-check text-center">
                                                                    <input class="form-check-input" type="radio" name="q_<?= $question['id_pertanyaan'] ?>" value="3">
                                                                    <label class="form-check-label d-block small">3<br>Puas</label>
                                                                </div>
                                                                <div class="form-check text-center">
                                                                    <input class="form-check-input" type="radio" name="q_<?= $question['id_pertanyaan'] ?>" value="4">
                                                                    <label class="form-check-label d-block small">4<br>Sangat<br>Puas</label>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    <?php else: ?>
                                        <p class="text-muted mb-0">Belum ada pertanyaan untuk unsur ini.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">Belum ada unsur kuesioner yang aktif.</p>
                    <?php endif; ?>
                </div>

                <!-- Saran/Masukan -->
                <div class="mb-4">
                    <h6 class="fw-bold border-bottom pb-2">C. SARAN/MASUKAN</h6>
                    <textarea class="form-control" rows="4" placeholder="Tuliskan saran atau masukan Anda untuk perbaikan pelayanan kami..."></textarea>
                </div>

                <div class="alert alert-warning">
                    <strong><i class="fas fa-exclamation-triangle me-1"></i>Catatan:</strong>
                    Ini hanya mode preview. Untuk mengisi survei sebenarnya, silakan scan QR code atau kunjungi halaman survei publik.
                </div>
            </form>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print { display: none !important; }
    .card { box-shadow: none !important; border: 1px solid #ddd !important; }
    body { background: white !important; }
}
.border-left-primary {
    border-left: 4px solid #0d6efd !important;
}
</style>

<?= $this->endSection() ?>
