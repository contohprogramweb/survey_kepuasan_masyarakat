<?= $this->extend('templates/admin_layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <h4><i class="fas fa-key"></i> <?= esc($title) ?></h4>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="permissionsTable">
                    <thead class="table-dark">
                        <tr>
                            <th>Module</th>
                            <th>Permission Key</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($permissions as $module => $perms): ?>
                        <?php foreach ($perms as $key => $desc): ?>
                        <tr>
                            <td><strong><?= esc($module) ?></strong></td>
                            <td><code><?= esc($key) ?></code></td>
                            <td><?= esc($desc) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
