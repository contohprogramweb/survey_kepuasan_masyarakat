<?= $this->extend('templates/admin_layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <h4><i class="fas fa-user-tag"></i> <?= esc($title) ?></h4>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="rolesTable">
                    <thead class="table-dark">
                        <tr>
                            <th>Role</th>
                            <th>Nama</th>
                            <th>Deskripsi</th>
                            <th>Permissions</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $i = 1;
                        foreach ($roles as $roleName => $role): 
                        ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><strong><?= esc($role['name']) ?></strong></td>
                            <td><?= esc($role['description']) ?></td>
                            <td>
                                <?php if (in_array('*', $role['permissions'])): ?>
                                    <span class="badge bg-danger">All Permissions</span>
                                <?php else: ?>
                                    <?php foreach ($role['permissions'] as $perm): ?>
                                        <span class="badge bg-info me-1"><?= esc($perm) ?></span>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= site_url('admin/roles/' . $i) ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
