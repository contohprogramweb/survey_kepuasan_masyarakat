<?= $this->extend('templates/admin_layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <h4><i class="fas fa-file-contract"></i> <?= esc($title) ?></h4>
        </div>
    </div>

    <?php if (isset($message)): ?>
        <div class="alert alert-info"><?= esc($message) ?></div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-body">
            <?php if (empty($logs)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Belum ada audit log tersedia.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="auditLogsTable">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Table</th>
                                <th>Record ID</th>
                                <th>Old Value</th>
                                <th>New Value</th>
                                <th>IP Address</th>
                                <th>Timestamp</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= esc($log['id'] ?? '-') ?></td>
                                <td><?= esc($log['user_name'] ?? 'System') ?></td>
                                <td><span class="badge bg-info"><?= esc($log['action'] ?? '-') ?></span></td>
                                <td><code><?= esc($log['table_name'] ?? '-') ?></code></td>
                                <td><?= esc($log['record_id'] ?? '-') ?></td>
                                <td><small><pre class="bg-light p-1"><?= esc(json_encode($log['old_value'] ?? [], JSON_PRETTY_PRINT)) ?></pre></small></td>
                                <td><small><pre class="bg-light p-1"><?= esc(json_encode($log['new_value'] ?? [], JSON_PRETTY_PRINT)) ?></pre></small></td>
                                <td><?= esc($log['ip_address'] ?? '-') ?></td>
                                <td><?= esc($log['created_at'] ?? '-') ?></td>
                                <td>
                                    <a href="<?= site_url('admin/audit-logs/' . ($log['id'] ?? '#')) ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> Detail
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
