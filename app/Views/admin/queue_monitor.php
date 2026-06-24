<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-3 text-gray-800">
                <i class="fas fa-tasks me-2"></i>Queue Monitor
            </h1>
            <p class="text-muted">Dashboard monitoring queue system dan worker status</p>
        </div>
    </div>

    <!-- Queue Statistics Cards -->
    <div class="row" id="queue-stats-cards">
        <?php foreach ($queues as $queueName): ?>
            <?php 
            $stats = $queueStats[$queueName] ?? ['pending' => 0, 'processing' => 0, 'completed' => 0, 'failed' => 0];
            $pendingClass = $stats['pending'] > 100 ? 'bg-danger' : ($stats['pending'] > 50 ? 'bg-warning' : 'bg-success');
            ?>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    <?= ucfirst(str_replace('-', ' ', $queueName)) ?>
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <span class="badge <?= $pendingClass ?>"><?= $stats['pending'] ?></span> Pending
                                </div>
                                <small class="text-muted">
                                    Processing: <?= $stats['processing'] ?> | 
                                    Completed: <?= $stats['completed'] ?> | 
                                    Failed: <?= $stats['failed'] ?>
                                </small>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="btn-group btn-group-sm w-100">
                            <?php if (isset($queueStats[$queueName])): ?>
                                <button class="btn btn-outline-primary pause-btn" data-queue="<?= $queueName ?>">
                                    <i class="fas fa-pause"></i> Pause
                                </button>
                                <button class="btn btn-outline-success resume-btn" data-queue="<?= $queueName ?>">
                                    <i class="fas fa-play"></i> Resume
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Failed Jobs Alert -->
    <?php if (($queueStats['failed_jobs']['count'] ?? 0) > 0): ?>
    <div class="row">
        <div class="col-12">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong><i class="fas fa-exclamation-triangle me-2"></i>Warning!</strong>
                Terdapat <?= $queueStats['failed_jobs']['count'] ?> job yang failed.
                <button type="button" class="btn btn-sm btn-danger ms-2" id="retry-all-btn">
                    <i class="fas fa-redo"></i> Retry All
                </button>
                <button type="button" class="btn btn-sm btn-info ms-1" id="view-failed-btn">
                    <i class="fas fa-list"></i> View Details
                </button>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Worker Status Table -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-workers me-2"></i>Worker Status
                    </h6>
                    <span class="badge bg-info" id="worker-count"><?= count($workers) ?> Active Workers</span>
                </div>
                <div class="card-body">
                    <?php if (empty($workers)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-workers fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No active workers found</p>
                            <p class="small text-muted">Start a worker with: <code>php spark queue:work --daemon</code></p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Worker ID</th>
                                        <th>Queue</th>
                                        <th>PID</th>
                                        <th>Status</th>
                                        <th>Memory</th>
                                        <th>Uptime</th>
                                        <th>Last Heartbeat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($workers as $worker): ?>
                                        <?php 
                                        $statusClass = ($worker['status'] ?? '') === 'active' ? 'badge bg-success' : 'badge bg-warning';
                                        $memoryMB = round(($worker['memory_usage'] ?? 0) / 1024 / 1024, 2);
                                        ?>
                                        <tr>
                                            <td><code><?= substr($worker['worker_id'] ?? '', 0, 40) ?>...</code></td>
                                            <td><span class="badge bg-secondary"><?= $worker['queue'] ?? 'unknown' ?></span></td>
                                            <td><?= $worker['pid'] ?? '?' ?></td>
                                            <td><span class="<?= $statusClass ?>"><?= $worker['status'] ?? 'unknown' ?></span></td>
                                            <td><?= $memoryMB ?> MB</td>
                                            <td><?= $worker['started_at'] ?? '-' ?></td>
                                            <td><?= $worker['last_heartbeat'] ?? '-' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Refresh Button -->
    <div class="row">
        <div class="col-12 text-end">
            <button class="btn btn-primary" id="refresh-btn">
                <i class="fas fa-sync-alt"></i> Refresh Data
            </button>
            <div class="form-check form-switch d-inline-block ms-3">
                <input class="form-check-input" type="checkbox" id="auto-refresh">
                <label class="form-check-label" for="auto-refresh">Auto Refresh (5s)</label>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Failed Jobs -->
<div class="modal fade" id="failedJobsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Failed Jobs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="failed-jobs-list">Loading...</div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Auto refresh interval
    let autoRefreshInterval = null;

    // Refresh data function
    function refreshData() {
        $.ajax({
            url: '<?= site_url('admin/queue/stats') ?>',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    updateQueueStats(response.data);
                    updateWorkerStatus(response.workers);
                }
            }
        });
    }

    // Update queue stats cards
    function updateQueueStats(stats) {
        // Update individual queue stats
        for (const [queueName, queueStats] of Object.entries(stats)) {
            if (queueName === 'failed_jobs') continue;
            
            const card = $(`.card`).filter(function() {
                return $(this).text().includes(queueName.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase()));
            });
            
            if (card.length) {
                const pendingBadge = card.find('.badge');
                const pendingCount = queueStats.pending || 0;
                
                pendingBadge.text(pendingCount);
                pendingBadge.removeClass('bg-success bg-warning bg-danger');
                
                if (pendingCount > 100) {
                    pendingBadge.addClass('bg-danger');
                } else if (pendingCount > 50) {
                    pendingBadge.addClass('bg-warning');
                } else {
                    pendingBadge.addClass('bg-success');
                }
            }
        }

        // Update failed jobs count
        const failedCount = stats.failed_jobs?.count || 0;
        $('#worker-count').text(`${Object.keys(stats).length - 1} Queues`);
    }

    // Update worker status
    function updateWorkerStatus(workers) {
        $('#worker-count').text(`${workers.length} Active Workers`);
        // Could add more detailed updates here
    }

    // Manual refresh button
    $('#refresh-btn').click(function() {
        $(this).find('i').addClass('fa-spin');
        refreshData();
        setTimeout(() => {
            $(this).find('i').removeClass('fa-spin');
        }, 1000);
    });

    // Auto refresh toggle
    $('#auto-refresh').change(function() {
        if (this.checked) {
            autoRefreshInterval = setInterval(refreshData, 5000);
            refreshData();
        } else {
            clearInterval(autoRefreshInterval);
        }
    });

    // Pause queue button
    $('.pause-btn').click(function() {
        const queueName = $(this).data('queue');
        if (confirm(`Pause queue "${queueName}"?`)) {
            $.post('<?= site_url('admin/queue/pause/') ?>' + queueName, function(response) {
                if (response.success) {
                    Swal.fire('Success', response.message, 'success');
                    refreshData();
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            });
        }
    });

    // Resume queue button
    $('.resume-btn').click(function() {
        const queueName = $(this).data('queue');
        $.post('<?= site_url('admin/queue/resume/') ?>' + queueName, function(response) {
            if (response.success) {
                Swal.fire('Success', response.message, 'success');
                refreshData();
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        });
    });

    // Retry all failed jobs
    $('#retry-all-btn').click(function() {
        if (confirm('Retry all failed jobs?')) {
            $.post('<?= site_url('admin/queue/retry/all') ?>', function(response) {
                Swal.fire('Success', 'All failed jobs queued for retry', 'success');
                refreshData();
            });
        }
    });

    // View failed jobs
    $('#view-failed-btn').click(function() {
        $('#failedJobsModal').modal('show');
        $.get('<?= site_url('admin/queue/failed-jobs') ?>', function(response) {
            if (response.success) {
                let html = '<ul class="list-group">';
                response.data.forEach(job => {
                    html += `
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <code>${job.payload_decoded?.job_id || 'N/A'}</code>
                                <br><small class="text-muted">${job.payload_decoded?.queue || 'unknown'} - ${job.created_at}</small>
                            </div>
                            <button class="btn btn-sm btn-primary retry-job-btn" data-job-id="${job.payload_decoded?.job_id}">
                                Retry
                            </button>
                        </li>
                    `;
                });
                html += '</ul>';
                $('#failed-jobs-list').html(html);
            }
        });
    });

    // Retry single job (delegated event)
    $(document).on('click', '.retry-job-btn', function() {
        const jobId = $(this).data('job-id');
        $.post('<?= site_url('admin/queue/retry/') ?>' + jobId, function(response) {
            if (response.success) {
                Swal.fire('Success', response.message, 'success');
                refreshData();
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        });
    });

    // Initial load
    refreshData();
});
</script>
<?= $this->endSection() ?>
