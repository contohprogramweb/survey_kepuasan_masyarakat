<!DOCTYPE html>
<html lang="<?= $currentLocale ?? 'id' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- SEO Meta Tags -->
    <title><?= esc($title ?? 'Dashboard Transparansi IKM') ?></title>
    <meta name="description" content="Dashboard transparansi Indeks Kepuasan Masyarakat (IKM) - Data terbuka untuk mengukur kualitas pelayanan publik">
    <meta name="keywords" content="IKM, Indeks Kepuasan Masyarakat, Pelayanan Publik, Survei Kepuasan, Dashboard Transparansi">
    <link rel="canonical" href="<?= current_url() ?>">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= current_url() ?>">
    <meta property="og:title" content="<?= esc($title ?? 'Dashboard Transparansi IKM') ?>">
    <meta property="og:description" content="Dashboard transparansi Indeks Kepuasan Masyarakat (IKM) - Data terbuka untuk mengukur kualitas pelayanan publik">
    <meta property="og:image" content="<?= base_url('assets/img/og-image.jpg') ?>">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?= current_url() ?>">
    <meta property="twitter:title" content="<?= esc($title ?? 'Dashboard Transparansi IKM') ?>">
    <meta property="twitter:description" content="Dashboard transparansi Indeks Kepuasan Masyarakat (IKM) - Data terbuka untuk mengukur kualitas pelayanan publik">
    
    <!-- JSON-LD Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Dataset",
        "name": "Dashboard Transparansi IKM - Indeks Kepuasan Masyarakat",
        "description": "Data transparansi Indeks Kepuasan Masyarakat (IKM) untuk mengukur kualitas pelayanan publik",
        "url": "<?= current_url() ?>",
        "keywords": "IKM, Indeks Kepuasan Masyarakat, Pelayanan Publik, Survei Kepuasan",
        "publisher": {
            "@type": "GovernmentOrganization",
            "name": "Pemerintah Daerah",
            "url": "<?= base_url() ?>"
        },
        "temporalCoverage": "<?= $filters['tahun'] ?>/<?= $filters['tahun'] ?>",
        "variableMeasured": [
            {
                "@type": "PropertyValue",
                "name": "Nilai IKM",
                "unitText": "Skala 0-100"
            },
            {
                "@type": "PropertyValue",
                "name": "Mutu Pelayanan",
                "unitText": "Kategorikal"
            }
        ]
    }
    </script>
    
    <!-- CDN Resources -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --bg-light: #f9fafb;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            background-color: var(--bg-light);
        }
        
        a:focus, button:focus {
            outline: 3px solid var(--primary-color);
            outline-offset: 2px;
        }
        
        .skip-link {
            position: absolute;
            top: -40px;
            left: 0;
            background: var(--primary-color);
            color: white;
            padding: 8px 16px;
            z-index: 10000;
        }
        
        .skip-link:focus {
            top: 0;
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            text-align: center;
            transition: transform 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
        }
        
        .stat-card h3 {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 8px;
            font-weight: 700;
        }
        
        .stat-card p {
            color: var(--text-light);
            font-size: 0.95rem;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
        }
        
        .filter-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>
    <a href="#main-content" class="skip-link">Langsung ke konten utama</a>
    
    <header class="dashboard-header">
        <div class="container">
            <h1 class="mb-3"><i class="fas fa-chart-line me-2"></i>Dashboard Transparansi IKM</h1>
            <p class="lead">Indeks Kepuasan Masyarakat - Data Terbuka untuk Pelayanan yang Lebih Baik</p>
        </div>
    </header>
    
    <main id="main-content" role="main">
        <section class="py-5">
            <div class="container">
                <!-- Filter Section -->
                <div class="filter-section">
                    <form method="get" action="<?= current_url() ?>" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="unit_id" class="form-label fw-semibold">Unit Layanan</label>
                            <select name="unit_id" id="unit_id" class="form-select">
                                <option value="">Semua Unit</option>
                                <?php foreach ($units as $unit): ?>
                                    <option value="<?= $unit['id'] ?>" <?= ($filters['unit_id'] == $unit['id']) ? 'selected' : '' ?>>
                                        <?= esc($unit['nama_unit']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="tahun" class="form-label fw-semibold">Tahun</label>
                            <select name="tahun" id="tahun" class="form-select">
                                <?php 
                                $currentYear = date('Y');
                                for ($y = $currentYear; $y >= $currentYear - 3; $y--): 
                                ?>
                                    <option value="<?= $y ?>" <?= ($filters['tahun'] == $y) ? 'selected' : '' ?>>
                                        <?= $y ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="periode_id" class="form-label fw-semibold">Periode</label>
                            <select name="periode_id" id="periode_id" class="form-select">
                                <option value="">Semua Periode</option>
                                <?php foreach ($periodes as $periode): ?>
                                    <option value="<?= $periode['id'] ?>" <?= ($filters['periode_id'] == $periode['id']) ? 'selected' : '' ?>>
                                        <?= esc($periode['nama_periode']) ?> <?= $periode['tahun'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                        </div>
                        <div class="col-md-2">
                            <button type="button" id="btn-refresh" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-sync-alt me-1"></i> Refresh
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="stat-card">
                            <h3><i class="fas fa-users text-primary"></i></h3>
                            <h3 id="total-responden"><?= number_format($summary['total_responden']) ?></h3>
                            <p>Total Responden</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-card">
                            <h3><i class="fas fa-star text-warning"></i></h3>
                            <h3 id="nilai-ikm"><?= number_format($summary['nilai_ikm'], 2) ?></h3>
                            <p>Nilai IKM Rata-rata</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-card">
                            <h3><i class="fas fa-award text-success"></i></h3>
                            <h3 id="mutu-pelayanan"><?= esc($summary['mutu_pelayanan']) ?></h3>
                            <p>Mutu Pelayanan</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-card">
                            <h3><i class="fas fa-building text-info"></i></h3>
                            <h3><?= count($units) ?></h3>
                            <p>Unit Layanan</p>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row mb-4">
                    <div class="col-lg-8 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h6 class="mb-0 fw-bold"><i class="fas fa-chart-line me-2"></i>Tren IKM</h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="chartTrenIkm"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h6 class="mb-0 fw-bold"><i class="fas fa-chart-pie me-2"></i>Distribusi per Unsur</h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="chartDistribusi"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rekapitulasi Table -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-table me-2"></i>Rekapitulasi per Periode</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Periode</th>
                                        <th>Tahun</th>
                                        <th class="text-center">Total Responden</th>
                                        <th class="text-center">Nilai IKM</th>
                                        <th class="text-center">Mutu Pelayanan</th>
                                        <th class="text-center">Delta</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($rekap)): ?>
                                        <?php foreach ($rekap as $row): ?>
                                            <tr>
                                                <td><?= esc($row['nama_periode']) ?></td>
                                                <td><?= esc($row['tahun']) ?></td>
                                                <td class="text-center"><?= number_format($row['total_responden']) ?></td>
                                                <td class="text-center">
                                                    <span class="badge bg-primary rounded-pill"><?= number_format($row['nilai_ikm'], 2) ?></span>
                                                </td>
                                                <td class="text-center">
                                                    <?php 
                                                    $badgeClass = match($row['mutu_pelayanan']) {
                                                        'Sangat Baik' => 'bg-success',
                                                        'Baik' => 'bg-primary',
                                                        'Kurang Baik' => 'bg-warning',
                                                        default => 'bg-danger'
                                                    };
                                                    ?>
                                                    <span class="badge <?= $badgeClass ?> rounded-pill"><?= esc($row['mutu_pelayanan']) ?></span>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($row['delta'] > 0): ?>
                                                        <span class="text-success"><i class="fas fa-arrow-up"></i> <?= number_format($row['delta'], 2) ?></span>
                                                    <?php elseif ($row['delta'] < 0): ?>
                                                        <span class="text-danger"><i class="fas fa-arrow-down"></i> <?= number_format(abs($row['delta']), 2) ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">
                                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                                <p>Belum ada data yang dipublikasikan</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Back to Home -->
                <div class="text-center mt-5">
                    <a href="<?= base_url() ?>" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Beranda
                    </a>
                </div>
            </div>
        </section>
    </main>
    
    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <p class="mb-0">&copy; <?= date('Y') ?> Pemerintah Daerah. All rights reserved.</p>
            <small class="text-muted">Dashboard Transparansi IKM - Data diperbarui setiap periode survei</small>
        </div>
    </footer>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Data dari server-side rendering
        let trenData = <?= json_encode($tren ?? []) ?>;
        let distData = <?= json_encode($distribusi ?? []) ?>;

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
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { 
                        beginAtZero: false, 
                        min: 50, 
                        max: 100,
                        ticks: { stepSize: 5 }
                    }
                },
                plugins: {
                    legend: { display: true },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'IKM: ' + context.parsed.y;
                            }
                        }
                    }
                }
            }
        });

        // --- Chart Distribusi Unsur ---
        const ctxDist = document.getElementById('chartDistribusi').getContext('2d');
        const labelsDist = distData.map(d => d.nama_unsur);
        const dataDist = distData.map(d => d.total_jawaban);
        const colors = ['#2563eb', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4'];

        new Chart(ctxDist, {
            type: 'doughnut',
            data: {
                labels: labelsDist,
                datasets: [{
                    data: dataDist,
                    backgroundColor: colors.slice(0, labelsDist.length),
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { 
                        position: 'bottom',
                        labels: { boxWidth: 12, font: { size: 11 } }
                    }
                }
            }
        });

        // --- AJAX Refresh Logic ---
        document.getElementById('btn-refresh').addEventListener('click', function() {
            const btn = this;
            const originalContent = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Loading...';

            const params = new URLSearchParams(window.location.search);
            
            fetch('<?= site_url('public/dashboard/data') ?>?' + params.toString())
                .then(response => response.json())
                .then(res => {
                    if (res.status === 'success') {
                        // Update summary cards
                        document.getElementById('total-responden').textContent = 
                            new Intl.NumberFormat('id-ID').format(res.data.summary.total_responden);
                        document.getElementById('nilai-ikm').textContent = 
                            res.data.summary.nilai_ikm.toFixed(2);
                        document.getElementById('mutu-pelayanan').textContent = 
                            res.data.summary.mutu_pelayanan;
                        
                        // Reload page untuk update charts (atau bisa update manual)
                        window.location.reload();
                    }
                })
                .catch(err => console.error('Error:', err))
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = originalContent;
                });
        });
    });
    </script>
</body>
</html>
