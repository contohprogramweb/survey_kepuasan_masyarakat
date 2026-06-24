<?= $this->extend('templates/admin_layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3"><?= esc($title) ?></h1>
                <a href="<?= site_url('admin/survey-questions/new') ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Pertanyaan Baru
                </a>
            </div>
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

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered" id="questionsTable">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="10%">Unsur</th>
                            <th width="40%">Pertanyaan</th>
                            <th width="12%">Tipe Input</th>
                            <th width="8%">Urutan</th>
                            <th width="7%">Wajib</th>
                            <th width="8%">Status</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($questions)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted">Belum ada pertanyaan survei. Klik tombol "Tambah Pertanyaan Baru" untuk membuat.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($questions as $index => $question): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><small class="text-muted"><?= esc($question['nama_unsur'] ?? '-') ?></small></td>
                                    <td><?= esc($question['pertanyaan']) ?></td>
                                    <td>
                                        <?php
                                        $typeLabels = [
                                            'rating' => 'Rating',
                                            'scale' => 'Skala',
                                            'text' => 'Teks',
                                            'textarea' => 'Textarea',
                                            'multiple_choice' => 'Pilihan Ganda'
                                        ];
                                        ?>
                                        <span class="badge bg-info"><?= esc($typeLabels[$question['tipe_input']] ?? $question['tipe_input']) ?></span>
                                    </td>
                                    <td><?= esc($question['urutan']) ?></td>
                                    <td>
                                        <?php if ($question['wajib_diisi']): ?>
                                            <span class="badge bg-warning">Ya</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Tidak</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($question['is_active']): ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= site_url('admin/survey-questions/' . $question['id_pertanyaan'] . '/edit') ?>" class="btn btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-info" onclick="toggleStatus(<?= $question['id_pertanyaan'] ?>)" title="Toggle Status">
                                                <i class="fas fa-sync"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger" onclick="deleteQuestion(<?= $question['id_pertanyaan'] ?>)" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function toggleStatus(id) {
    if (!confirm('Apakah Anda yakin ingin mengubah status pertanyaan ini?')) return;
    
    fetch('<?= site_url('admin/survey-questions') ?>/' + id + '/toggle-status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message);
        }
    });
}

function deleteQuestion(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus pertanyaan ini? Tindakan ini tidak dapat dibatalkan.')) return;
    
    fetch('<?= site_url('admin/survey-questions') ?>/' + id, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (response.ok) {
            location.reload();
        } else {
            return response.json().then(err => { throw err; });
        }
    })
    .catch(error => {
        alert('Gagal menghapus pertanyaan: ' + (error.message || 'Terjadi kesalahan'));
    });
}
</script>
<?= $this->endSection() ?>
