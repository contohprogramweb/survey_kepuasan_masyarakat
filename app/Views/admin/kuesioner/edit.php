<?= $this->extend('templates/admin_layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0 text-gray-800"><?= esc($title) ?></h1>
                <a href="<?= site_url('admin/kuesioner') ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Edit Unsur Kuesioner</h6>
        </div>
        <div class="card-body">
            <?php if (isset($validation) && $validation->getErrors()): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($validation->getErrors() as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="<?= site_url('admin/kuesioner/update/' . $element['id_kuesioner']) ?>" method="post">
                <?= csrf_field() ?>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="kode_unsur" class="form-label">Kode Unsur <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control <?= session('errors.kode_unsur') ? 'is-invalid' : '' ?>" 
                               id="kode_unsur" 
                               name="kode_unsur" 
                               value="<?= old('kode_unsur', $element['kode_unsur']) ?>"
                               placeholder="Contoh: U1"
                               required>
                        <div class="invalid-feedback">
                            <?= session('errors.kode_unsur') ?>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="urutan" class="form-label">Urutan <span class="text-danger">*</span></label>
                        <input type="number" 
                               class="form-control <?= session('errors.urutan') ? 'is-invalid' : '' ?>" 
                               id="urutan" 
                               name="urutan" 
                               value="<?= old('urutan', $element['urutan']) ?>"
                               min="1"
                               required>
                        <div class="invalid-feedback">
                            <?= session('errors.urutan') ?>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="nama_unsur" class="form-label">Nama Unsur <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control <?= session('errors.nama_unsur') ? 'is-invalid' : '' ?>" 
                           id="nama_unsur" 
                           name="nama_unsur" 
                           value="<?= old('nama_unsur', $element['nama_unsur']) ?>"
                           placeholder="Contoh: Persyaratan Pelayanan"
                           required>
                    <div class="invalid-feedback">
                        <?= session('errors.nama_unsur') ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <textarea class="form-control <?= session('errors.deskripsi') ? 'is-invalid' : '' ?>" 
                              id="deskripsi" 
                              name="deskripsi" 
                              rows="3"
                              placeholder="Deskripsi unsur (opsional)"><?= old('deskripsi', $element['deskripsi'] ?? '') ?></textarea>
                    <div class="invalid-feedback">
                        <?= session('errors.deskripsi') ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="bobot" class="form-label">Bobot</label>
                        <input type="number" 
                               step="0.001" 
                               class="form-control <?= session('errors.bobot') ? 'is-invalid' : '' ?>" 
                               id="bobot" 
                               name="bobot" 
                               value="<?= old('bobot', $element['bobot'] ?? 0.111) ?>"
                               min="0"
                               max="1">
                        <div class="invalid-feedback">
                            <?= session('errors.bobot') ?>
                        </div>
                        <small class="text-muted">Default: 0.111 (equal weight untuk 9 unsur)</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select <?= session('errors.status') ? 'is-invalid' : '' ?>" 
                                id="status" 
                                name="status">
                            <option value="aktif" <?= old('status', $element['status']) === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                            <option value="nonaktif" <?= old('status', $element['status']) === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                        </select>
                        <div class="invalid-feedback">
                            <?= session('errors.status') ?>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update Unsur
                    </button>
                    <a href="<?= site_url('admin/kuesioner') ?>" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Questions Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Pertanyaan untuk Unsur Ini</h6>
            <a href="<?= site_url('admin/survey-questions/new?element=' . $element['id_kuesioner']) ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Tambah Pertanyaan
            </a>
        </div>
        <div class="card-body">
            <?php
            $surveiModel = new \App\Models\SurveiModel();
            $questions = $surveiModel->getQuestionsByElement($element['id_kuesioner']);
            ?>
            
            <?php if (empty($questions)): ?>
                <p class="text-muted mb-0">Belum ada pertanyaan untuk unsur ini.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="50%">Pertanyaan</th>
                                <th width="20%">Tipe Input</th>
                                <th width="15%">Urutan</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($questions as $index => $question): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= esc($question['pertanyaan']) ?></td>
                                    <td><span class="badge bg-info"><?= esc($question['tipe_input']) ?></span></td>
                                    <td><?= $question['urutan'] ?></td>
                                    <td>
                                        <a href="<?= site_url('admin/survey-questions/edit/' . $question['id_pertanyaan']) ?>" 
                                           class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
