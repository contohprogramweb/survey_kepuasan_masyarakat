<?= $this->extend('app/Views/templates/admin_layout') ?>

<?= $this->section('title') ?>Laporan IKM<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Laporan Indeks Kepuasan Masyarakat (IKM)</h1>
        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" onclick="showHelp()">
            <i class="fas fa-question-circle fa-sm"></i> Bantuan
        </a>
    </div>

    <!-- Alert Messages -->
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <!-- Filter Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Laporan</h6>
        </div>
        <div class="card-body">
            <form id="filterForm" method="GET">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="id_unit">Unit Layanan</label>
                            <select class="form-control" id="id_unit" name="id_unit">
                                <option value="">Semua Unit</option>
                                <?php foreach ($units as $unit): ?>
                                    <option value="<?= $unit['id_unit'] ?>"><?= htmlspecialchars($unit['nama_unit']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="id_periode">Periode</label>
                            <select class="form-control" id="id_periode" name="id_periode">
                                <option value="">Semua Periode</option>
                                <?php foreach ($periodes as $periode): ?>
                                    <option value="<?= $periode['id_periode'] ?>"><?= htmlspecialchars($periode['nama_periode']) ?> (<?= $periode['tahun'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="start_date">Tanggal Mulai</label>
                            <input type="date" class="form-control" id="start_date" name="start_date">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="end_date">Tanggal Akhir</label>
                            <input type="date" class="form-control" id="end_date" name="end_date">
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-info mr-2" onclick="applyFilter()">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="resetFilter()">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Export Actions Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Ekspor Laporan</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-file-pdf text-danger"></i> Ekspor PDF</h5>
                            <p class="card-text">Generate laporan dalam format PDF dengan layout resmi sesuai standar IKM.</p>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-danger" onclick="generateReport('pdf', 'direct')">
                                    <i class="fas fa-download"></i> Download Langsung
                                </button>
                                <button type="button" class="btn btn-outline-danger" onclick="generateReport('pdf', 'queue')">
                                    <i class="fas fa-clock"></i> Via Queue (Data Besar)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-file-excel text-success"></i> Ekspor Excel</h5>
                            <p class="card-text">Export data ke Excel dengan multiple sheet: rekapitulasi, detail responden, dan metadata.</p>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-success" onclick="generateReport('excel', 'direct')">
                                    <i class="fas fa-download"></i> Download Langsung
                                </button>
                                <button type="button" class="btn btn-outline-success" onclick="generateReport('excel', 'queue')">
                                    <i class="fas fa-clock"></i> Via Queue (Data Besar)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Preview Laporan</h6>
            <button type="button" class="btn btn-sm btn-primary" onclick="loadPreview()">
                <i class="fas fa-eye"></i> Refresh Preview
            </button>
        </div>
        <div class="card-body">
            <iframe id="previewFrame" src="" style="width: 100%; height: 500px; border: 1px solid #ddd;"></iframe>
        </div>
    </div>

    <!-- Riwayat Job Queue -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Riwayat Generate Laporan</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="jobHistoryTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Job ID</th>
                            <th>Tipe</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                            <th>Selesai</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="jobHistoryBody">
                        <tr>
                            <td colspan="6" class="text-center">Memuat riwayat...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary" role="status"></div>
                <h5 class="mt-3" id="loadingText">Sedang memproses...</h5>
                <p class="text-muted">Mohon tunggu, laporan sedang dibuat.</p>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let currentFilters = {};

function getFilters() {
    return {
        id_unit: document.getElementById('id_unit').value,
        id_periode: document.getElementById('id_periode').value,
        start_date: document.getElementById('start_date').value,
        end_date: document.getElementById('end_date').value
    };
}

function applyFilter() {
    currentFilters = getFilters();
    loadPreview();
}

function resetFilter() {
    document.getElementById('filterForm').reset();
    currentFilters = {};
    loadPreview();
}

function loadPreview() {
    const filters = getFilters();
    const params = new URLSearchParams(filters).toString();
    document.getElementById('previewFrame').src = '<?= site_url('laporan/previewPdf') ?>?' + params;
}

function generateReport(type, mode) {
    const filters = getFilters();
    
    if (mode === 'direct') {
        // Direct download
        const endpoint = type === 'pdf' ? 'directPdf' : 'directExcel';
        const params = new URLSearchParams(filters).toString();
        window.location.href = '<?= site_url('laporan') ?>' + '/' + endpoint + '?' + params;
    } else {
        // Via queue
        showLoading('Mengirim permintaan ke queue...');
        
        const endpoint = type === 'pdf' ? 'generatePdf' : 'generateExcel';
        
        fetch('<?= site_url('laporan/' . '') ?>' + endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(filters)
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                alert(data.message);
                loadJobHistory();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            hideLoading();
            alert('Terjadi kesalahan: ' + error);
        });
    }
}

function showLoading(text) {
    document.getElementById('loadingText').textContent = text;
    $('#loadingModal').modal('show');
}

function hideLoading() {
    $('#loadingModal').modal('hide');
}

function loadJobHistory() {
    fetch('<?= site_url('laporan/history') ?>')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('jobHistoryBody');
            if (data.data && data.data.length > 0) {
                let html = '';
                data.data.forEach(job => {
                    const type = job.queue_name.includes('pdf') ? 'PDF' : 'Excel';
                    const statusClass = getStatusClass(job.status);
                    const resultData = job.result_data ? JSON.parse(job.result_data || '{}') : null;
                    const filename = resultData?.file_name || '-';
                    
                    html += `
                        <tr>
                            <td><small>${job.payload_data?.job_id || '-'}</small></td>
                            <td>${type}</td>
                            <td><span class="badge badge-${statusClass}">${job.status}</span></td>
                            <td>${job.created_at}</td>
                            <td>${job.completed_at || '-'}</td>
                            <td>
                                ${resultData?.file_path ? 
                                    `<a href="<?= site_url('laporan/download') ?>/${type.toLowerCase()}/${filename}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-download"></i>
                                    </a>` : 
                                    '<span class="text-muted">-</span>'
                                }
                            </td>
                        </tr>
                    `;
                });
                tbody.innerHTML = html;
            } else {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center">Belum ada riwayat</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error loading history:', error);
        });
}

function getStatusClass(status) {
    switch(status) {
        case 'completed': return 'success';
        case 'processing': return 'info';
        case 'failed': return 'danger';
        default: return 'secondary';
    }
}

function showHelp() {
    alert('Bantuan Laporan IKM:\n\n1. Pilih filter untuk membatasi data laporan\n2. Preview menampilkan hasil sementara\n3. Download Langsung: untuk data kecil (< 1000 record)\n4. Via Queue: untuk data besar, file akan diproses di background\n5. Cek Riwayat untuk melihat status dan download file yang sudah jadi');
}

// Load initial data
document.addEventListener('DOMContentLoaded', function() {
    loadPreview();
    loadJobHistory();
    
    // Auto-refresh job history every 10 seconds
    setInterval(loadJobHistory, 10000);
});
</script>
<?= $this->endSection() ?>
