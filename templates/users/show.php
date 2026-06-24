<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail User</title>
    
    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .badge-purple {
            background-color: #6f42c1;
            color: white;
        }
        .audit-log-list {
            max-height: 300px;
            overflow-y: auto;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-user"></i> Detail User</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">ID</th>
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                            </tr>
                            <tr>
                                <th>Username</th>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                            </tr>
                            <tr>
                                <th>Nama Lengkap</th>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            </tr>
                            <tr>
                                <th>Role</th>
                                <td>
                                    <?php 
                                    $roleColors = [
                                        'super_admin' => 'danger',
                                        'admin' => 'warning',
                                        'operator' => 'info',
                                        'pimpinan' => 'primary',
                                        'dpo' => 'purple',
                                        'devops' => 'success',
                                    ];
                                    $color = $roleColors[$user['role']] ?? 'secondary';
                                    ?>
                                    <span class="badge badge-<?php echo $color; ?>">
                                        <?php echo htmlspecialchars(\App\Model\UserModel::ROLES[$user['role']] ?? $user['role']); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <?php 
                                    $statusColors = [
                                        'active' => 'success',
                                        'inactive' => 'secondary',
                                        'suspended' => 'danger',
                                    ];
                                    $color = $statusColors[$user['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge badge-<?php echo $color; ?>">
                                        <?php echo ucfirst(htmlspecialchars($user['status'])); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>MFA Enabled</th>
                                <td>
                                    <?php if ($user['mfa_enabled']): ?>
                                        <span class="badge badge-success"><i class="fas fa-check"></i> Yes</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary"><i class="fas fa-times"></i> No</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Dibuat</th>
                                <td>
                                    <?php echo date('d M Y H:i', strtotime($user['created_at'])); ?>
                                    <br>
                                    <small class="text-muted">oleh <?php echo htmlspecialchars($user['created_by_name'] ?? 'System'); ?></small>
                                </td>
                            </tr>
                            <tr>
                                <th>Terakhir Diupdate</th>
                                <td>
                                    <?php echo $user['updated_at'] ? date('d M Y H:i', strtotime($user['updated_at'])) : '-'; ?>
                                    <br>
                                    <small class="text-muted">oleh <?php echo htmlspecialchars($user['updated_by_name'] ?? '-'); ?></small>
                                </td>
                            </tr>
                            <tr>
                                <th>Diaktifkan</th>
                                <td><?php echo $user['activated_at'] ? date('d M Y H:i', strtotime($user['activated_at'])) : '-'; ?></td>
                            </tr>
                            <?php if ($user['deactivated_at']): ?>
                            <tr>
                                <th>Dinonaktifkan</th>
                                <td><?php echo date('d M Y H:i', strtotime($user['deactivated_at'])); ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($user['password_reset_at']): ?>
                            <tr>
                                <th>Password Terakhir Direset</th>
                                <td><?php echo date('d M Y H:i', strtotime($user['password_reset_at'])); ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                        
                        <hr>
                        
                        <div class="btn-group">
                            <a href="/users" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <?php if ($_SESSION['user_role'] ?? '' === 'super_admin'): ?>
                            <a href="/users/<?php echo $user['id']; ?>/edit" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-history"></i> Audit Log</h4>
                    </div>
                    <div class="card-body">
                        <?php if (empty($auditLogs)): ?>
                        <p class="text-muted text-center">Belum ada riwayat audit</p>
                        <?php else: ?>
                        <div class="audit-log-list">
                            <ul class="list-group list-group-flush">
                                <?php foreach ($auditLogs as $log): ?>
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <strong>
                                            <?php 
                                            $actionColors = [
                                                'CREATE' => 'success',
                                                'UPDATE' => 'info',
                                                'DELETE' => 'danger',
                                                'SOFT_DELETE' => 'warning',
                                                'ACTIVATE' => 'success',
                                                'DEACTIVATE' => 'secondary',
                                                'PASSWORD_RESET' => 'warning',
                                                'ROLE_CHANGE' => 'primary',
                                            ];
                                            $color = $actionColors[$log['action']] ?? 'secondary';
                                            ?>
                                            <span class="badge badge-<?php echo $color; ?>"><?php echo htmlspecialchars($log['action']); ?></span>
                                        </strong>
                                        <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($log['created_at'])); ?></small>
                                    </div>
                                    <div class="mt-1">
                                        <small class="text-muted">
                                            oleh: <?php echo htmlspecialchars($log['actor_name'] ?? $log['actor_username'] ?? 'Unknown'); ?>
                                        </small>
                                    </div>
                                    <?php if ($log['old_values']): ?>
                                    <div class="mt-1">
                                        <small><strong>Sebelum:</strong></small>
                                        <pre class="mb-0" style="font-size: 0.75rem;"><?php echo htmlspecialchars($log['old_values']); ?></pre>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($log['new_values']): ?>
                                    <div class="mt-1">
                                        <small><strong>Sesudah:</strong></small>
                                        <pre class="mb-0" style="font-size: 0.75rem;"><?php echo htmlspecialchars($log['new_values']); ?></pre>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($log['ip_address']): ?>
                                    <div class="mt-1">
                                        <small class="text-muted">
                                            <i class="fas fa-globe"></i> <?php echo htmlspecialchars($log['ip_address']); ?>
                                        </small>
                                    </div>
                                    <?php endif; ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
