<?= $this->extend('templates/admin_layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <!-- Header & Filter -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 text-gray-800">Dashboard Internal IKM</h4>
        <button id="btn-refresh" class="btn btn-sm btn-primary"><i class="fas fa-sync-alt"></i> Refresh Data</button>
    </div>

    <!-- Filter Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-white">
            <form id="filter-form" method="get" action="<?= site_url('admin/dashboard') ?>" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Unit Layanan</label>
                    <select name="unit_id" class="form-select form-select-sm">
                        <option value="">Semua Unit</option>
                        <?php foreach ($units as $unit): ?>
                            <option value="<?= $unit['id'] ?>" <?= ($filters['unit_id'] == $unit['id']) ? 'selected' : '' ?>><?= esc($unit['nama_unit']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tahun</label>
                    <select name="tahun" class="form-select form-select-sm">
                        <?php 
                        $currentYear = date('Y');
                        for ($y = $currentYear - 2; $y <= $currentYear + 1; $y++): 
                        ?>
                            <option value="<?= $y ?>" <?= ($filters['tahun'] == $y) ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Periode</label>
                    <select name="periode_id" class="form-select form-select-sm">
                        <option value="">Semua Periode</option>
                        <?php foreach ($periodes as $periode): ?>
                            <option value="<?= $periode['id'] ?>" <?= ($filters['periode_id'] == $periode['id']) ? 'selected' : '' ?>>
                                <?= esc($periode['nama_periode']) ?> <?= $periode['tahun'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-secondary btn-sm w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Alert Section -->
    <?php if (!empty($alerts)): ?>
    <div class="alert alert-danger d-flex align-items-center shadow-sm" role="alert">
        <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
        <div>
            <strong>Peringatan Penurunan IKM!</strong> Terdeteksi penurunan signifikan pada unit berikut:
            <ul class="mb-0 mt-1">
                <?php foreach ($alerts as $alert): ?>
                    <li><?= esc($alert['nama_unit']) ?> (Turun <?= number_format(abs($alert['selisih']), 2) ?> poin)</li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <!-- Summary Cards (Rekapitulasi Terbaru) -->
    <div class="row mb-4">
        <?php if (!empty($rekap)): 
            $latest = $rekap[0]; // Ambil data periode terbaru
        ?>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Responden (Terakhir)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($latest['total_responden']) ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-users fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Nilai IKM</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($latest['nilai_ikm'], 2) ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-chart-line fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Mutu Pelayanan</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $latest['mutu_pelayanan'] ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-award fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-<?= ($latest['delta'] >= 0) ? 'warning' : 'danger' ?> shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-<?= ($latest['delta'] >= 0) ? 'warning' : 'danger' ?> text-uppercase mb-1">Delta (vs Periode Lalu)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= ($latest['delta'] > 0 ? '+' : '') . number_format($latest['delta'], 2) ?>
                            </div>
                        </div>
                        <div class="col-auto"><i class="fas fa-arrow-<?= ($latest['delta'] >= 0) ? 'up' : 'down' ?> fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <!-- Tren IKM -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white">
                    <h6 class="m-0 font-weight-bold text-primary">Grafik Tren IKM</h6>
                </div>
                <div class="card-body">
                    <canvas id="chartTrenIkm" style="height: 300px; width: 100%;"></canvas>
                </div>
            </div>
        </div>

        <!-- Distribusi Unsur -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-primary">Distribusi per Unsur</h6>
                </div>
                <div class="card-body">
                    <canvas id="chartDistribusi" style="height: 250px; width: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js 4 CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Data dari PHP (Server Side Rendered awal)
    let trenData = <?= json_encode($tren) ?>;
    let distData = <?= json_encode($distribusi) ?>;

    // --- Chart Tren IKM ---
    const ctxTren = document.getElementById('chartTrenIkm').getContext('2d');
    const labelsTren = trenData.map(d => d.nama_periode + ' ' + d.tahun);
    const dataTren = trenData.map(d => parseFloat(d.ikm_avg).toFixed(2));

    new Chart(ctxTren, {
        type: 'line',
        data: {
            labels: labelsTren,
            datasets: [{
                label: 'Nilai IKM',
                data: dataTren,
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: false, min: 50, max: 100 }
            }
        }
    });

    // --- Chart Distribusi Unsur ---
    const ctxDist = document.getElementById('chartDistribusi').getContext('2d');
    const labelsDist = distData.map(d => d.nama_unsur);
    const dataDist = distData.map(d => d.total_jawaban);

    new Chart(ctxDist, {
        type: 'doughnut',
        data: {
            labels: labelsDist,
            datasets: [{
                data: dataDist,
                backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796'],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // --- AJAX Refresh Logic ---
    document.getElementById('btn-refresh').addEventListener('click', function() {
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';

        const params = new URLSearchParams(window.location.search);
        
        fetch('<?= site_url('api/dashboard/data') ?>?' + params.toString())
            .then(response => response.json())
            .then(res => {
                if(res.status === 'success') {
                    // Update charts manually atau reload halaman jika kompleks
                    // Di sini kita reload halaman untuk simplifikasi update semua elemen DOM
                    window.location.reload();
                }
            })
            .catch(err => console.error(err))
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh Data';
            });
    });
});
</script>
<?= $this->endSection() ?>
