<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan IKM</title>
    <style>
        /* Reset dan base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #333;
        }

        /* Header Styles */
        .header {
            display: table;
            width: 100%;
            border-bottom: 3px solid #4e73df;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header-logo {
            display: table-cell;
            width: 100px;
            vertical-align: middle;
        }

        .header-logo img {
            width: 80px;
            height: 80px;
            object-fit: contain;
        }

        .header-content {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
        }

        .header-content h1 {
            font-size: 16pt;
            font-weight: bold;
            color: #4e73df;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .header-content h2 {
            font-size: 14pt;
            font-weight: bold;
            color: #333;
            margin-bottom: 3px;
        }

        .header-content p {
            font-size: 10pt;
            color: #666;
            margin-bottom: 2px;
        }

        /* Laporan Info */
        .laporan-info {
            background-color: #f8f9fc;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #4e73df;
        }

        .laporan-info table {
            width: 100%;
            border-collapse: collapse;
        }

        .laporan-info td {
            padding: 5px 0;
        }

        .laporan-info td:first-child {
            font-weight: bold;
            width: 150px;
            color: #5a5c69;
        }

        /* Section Styles */
        .section {
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 13pt;
            font-weight: bold;
            color: #4e73df;
            border-bottom: 2px solid #4e73df;
            padding-bottom: 8px;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        /* Table Styles */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10pt;
        }

        .data-table thead th {
            background-color: #4e73df;
            color: white;
            padding: 10px 8px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #375a7f;
        }

        .data-table tbody td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: center;
        }

        .data-table tbody tr:nth-child(even) {
            background-color: #f8f9fc;
        }

        .data-table tbody tr:hover {
            background-color: #e9ecef;
        }

        /* Category Colors */
        .kategori-A {
            background-color: #28a745 !important;
            color: white;
            font-weight: bold;
        }

        .kategori-B {
            background-color: #17a2b8 !important;
            color: white;
            font-weight: bold;
        }

        .kategori-C {
            background-color: #ffc107 !important;
            color: #333;
            font-weight: bold;
        }

        .kategori-D {
            background-color: #dc3545 !important;
            color: white;
            font-weight: bold;
        }

        /* Summary Cards */
        .summary-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .summary-row {
            display: table-row;
        }

        .summary-card {
            display: table-cell;
            width: 25%;
            padding: 15px;
            text-align: center;
            border: 1px solid #ddd;
            margin-right: 10px;
        }

        .summary-card .value {
            font-size: 24pt;
            font-weight: bold;
            color: #4e73df;
            margin-bottom: 5px;
        }

        .summary-card .label {
            font-size: 9pt;
            color: #666;
            text-transform: uppercase;
        }

        /* Grafik Placeholder */
        .grafik-container {
            text-align: center;
            margin: 20px 0;
            padding: 20px;
            background-color: #f8f9fc;
            border: 1px dashed #ccc;
        }

        .grafik-placeholder {
            font-style: italic;
            color: #666;
            font-size: 10pt;
        }

        /* Kesimpulan */
        .kesimpulan {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-top: 20px;
        }

        .kesimpulan h4 {
            font-size: 12pt;
            font-weight: bold;
            color: #856404;
            margin-bottom: 10px;
        }

        .kesimpulan ul {
            margin-left: 20px;
        }

        .kesimpulan li {
            margin-bottom: 5px;
            color: #856404;
        }

        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #ddd;
            text-align: right;
            font-size: 9pt;
            color: #666;
        }

        .footer-signature {
            margin-top: 30px;
            display: table;
            width: 100%;
        }

        .signature-block {
            display: table-cell;
            width: 50%;
            text-align: center;
            padding: 20px;
        }

        .signature-line {
            margin-top: 60px;
            border-top: 1px solid #333;
            padding-top: 5px;
            font-weight: bold;
        }

        /* Page Break */
        .page-break {
            page-break-after: always;
        }

        /* Print optimizations */
        @media print {
            body {
                font-size: 10pt;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-logo">
            <?php if (file_exists($metadata['logo_path'] ?? '')): ?>
                <img src="<?= $metadata['logo_path'] ?>" alt="Logo Instansi">
            <?php else: ?>
                <div style="width:80px;height:80px;background:#4e73df;border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-weight:bold;">LOGO</div>
            <?php endif; ?>
        </div>
        <div class="header-content">
            <h1><?= $metadata['nama_instansi'] ?></h1>
            <h2>LAPORAN INDEKS KEPUASAN MASYARAKAT (IKM)</h2>
            <p><?= $metadata['alamat'] ?></p>
            <p>Telp: <?= $metadata['telepon'] ?> | Email: <?= $metadata['email'] ?></p>
            <p>Website: <?= $metadata['website'] ?></p>
        </div>
    </div>

    <!-- Laporan Info -->
    <div class="laporan-info">
        <table>
            <tr>
                <td>Tanggal Cetak</td>
                <td>: <?= date('d F Y H:i:s') ?></td>
            </tr>
            <?php if (!empty($filters['id_unit'])): ?>
            <tr>
                <td>Unit Layanan</td>
                <td>: <?= $filters['id_unit'] ?></td>
            </tr>
            <?php endif; ?>
            <?php if (!empty($filters['start_date']) || !empty($filters['end_date'])): ?>
            <tr>
                <td>Periode</td>
                <td>: <?= ($filters['start_date'] ?? 'Awal') ?> s/d <?= ($filters['end_date'] ?? 'Sekarang') ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- Ringkasan Eksekutif -->
    <div class="section">
        <div class="section-title">RINGKASAN EKSEKUTIF</div>
        
        <div class="summary-grid">
            <div class="summary-row">
                <div class="summary-card">
                    <div class="value"><?= number_format($stats['summary']['rata_ikm'] ?? 0, 2) ?></div>
                    <div class="label">Rata-rata IKM</div>
                </div>
                <div class="summary-card">
                    <div class="value"><?= $stats['summary']['total_periode'] ?? 0 ?></div>
                    <div class="label">Total Periode</div>
                </div>
                <div class="summary-card">
                    <div class="value"><?= $stats['summary']['total_responden'] ?? 0 ?></div>
                    <div class="label">Total Responden</div>
                </div>
                <div class="summary-card">
                    <div class="value"><?= number_format($stats['summary']['max_ikm'] ?? 0, 2) ?></div>
                    <div class="label">IKM Tertinggi</div>
                </div>
            </div>
        </div>

        <!-- Distribusi Kategori -->
        <h4 style="margin-bottom: 10px; color: #5a5c69;">Distribusi Kategori Mutu Layanan</h4>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Kategori</th>
                    <th>Predikat</th>
                    <th>Jumlah Periode</th>
                    <th>Persentase</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $totalPeriode = $stats['summary']['total_periode'] ?? 1;
                foreach ($stats['category_distribution'] ?? [] as $cat): 
                    $persentase = ($cat['jumlah'] / $totalPeriode) * 100;
                ?>
                <tr>
                    <td class="kategori-<?= $cat['kategori'] ?>"><?= $cat['kategori'] ?></td>
                    <td><?= $cat['predikat'] ?></td>
                    <td><?= $cat['jumlah'] ?></td>
                    <td><?= number_format($persentase, 1) ?>%</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Rekapitulasi per Periode -->
    <div class="section">
        <div class="section-title">REKAPITULASI NILAI IKM PER PERIODE</div>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Unit Layanan</th>
                    <th>Periode</th>
                    <th>Tahun</th>
                    <th>Nilai IKM</th>
                    <th>Kategori</th>
                    <th>Predikat</th>
                    <th>Responden</th>
                    <th>Delta</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                foreach ($rekapitulasi as $rekap): 
                    $deltaClass = '';
                    $deltaSign = '';
                    if ($rekap['delta_ikm'] !== null) {
                        if ($rekap['delta_ikm'] > 0) {
                            $deltaClass = 'color: #28a745;';
                            $deltaSign = '+';
                        } elseif ($rekap['delta_ikm'] < 0) {
                            $deltaClass = 'color: #dc3545;';
                            $deltaSign = '';
                        }
                    }
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td style="text-align: left;"><?= htmlspecialchars($rekap['nama_unit']) ?></td>
                    <td style="text-align: left;"><?= htmlspecialchars($rekap['nama_periode']) ?></td>
                    <td><?= $rekap['tahun'] ?></td>
                    <td style="font-weight: bold;"><?= number_format($rekap['nilai_ikm'], 2) ?></td>
                    <td class="kategori-<?= $rekap['kategori'] ?>"><?= $rekap['kategori'] ?></td>
                    <td><?= $rekap['predikat'] ?></td>
                    <td><?= $rekap['jumlah_responden'] ?></td>
                    <td style="<?= $deltaClass ?>">
                        <?= $rekap['delta_ikm'] !== null ? $deltaSign . number_format($rekap['delta_ikm'], 2) : '-' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <?php if (empty($rekapitulasi)): ?>
                <tr>
                    <td colspan="9" style="text-align: center; font-style: italic;">Tidak ada data untuk ditampilkan</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Detail Unsur -->
    <?php if (!empty($rekapitulasi) && !empty($rekapitulasi[0]['unsur_details'])): ?>
    <div class="page-break"></div>
    
    <div class="section">
        <div class="section-title">DETAIL NILAI PER UNSUR</div>
        
        <?php foreach ($rekapitulasi as $rekap): ?>
            <?php if (!empty($rekap['unsur_details'])): ?>
            <div style="margin-bottom: 20px; page-break-inside: avoid;">
                <h4 style="margin-bottom: 10px; color: #5a5c69;">
                    <?= htmlspecialchars($rekap['nama_unit']) ?> - <?= htmlspecialchars($rekap['nama_periode']) ?>
                </h4>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Kode Unsur</th>
                            <th>Nilai Rata-Rata (NRR)</th>
                            <th>Bobot</th>
                            <th>NRR Tertimbang</th>
                            <th>Jumlah Responden</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rekap['unsur_details'] as $unsur): ?>
                        <tr>
                            <td style="text-align: left;"><?= htmlspecialchars($unsur['unsur_code']) ?></td>
                            <td><?= number_format($unsur['nrr'], 4) ?></td>
                            <td><?= number_format($unsur['bobot'], 3) ?></td>
                            <td><?= number_format($unsur['nrr_tertimbang'], 4) ?></td>
                            <td><?= $unsur['response_count'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background-color: #4e73df; color: white; font-weight: bold;">
                            <td colspan="3" style="text-align: right;">TOTAL NILAI IKM:</td>
                            <td colspan="2" style="text-align: center;"><?= number_format($rekap['nilai_ikm'], 2) ?> (<?= $rekap['predikat'] ?>)</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Grafik -->
    <div class="section">
        <div class="section-title">GRAFIK TREN IKM</div>
        
        <div class="grafik-container">
            <div class="grafik-placeholder">
                <p><strong>Grafik Tren Nilai IKM per Periode</strong></p>
                <p>(Grafik akan ditampilkan pada versi digital dengan Chart.js atau library grafik lainnya)</p>
                <p style="margin-top: 20px;">
                    <?php 
                    // Simple ASCII representation for PDF
                    if (!empty($rekapitulasi)) {
                        echo "Periode | Nilai IKM<br>";
                        echo str_repeat("-", 40) . "<br>";
                        foreach (array_slice(array_reverse($rekapitulasi), 0, 5) as $rekap) {
                            $barLength = intval($rekap['nilai_ikm'] / 2);
                            echo htmlspecialchars(substr($rekap['nama_periode'], 0, 15)) . " | ";
                            echo str_repeat("█", $barLength) . " " . number_format($rekap['nilai_ikm'], 1) . "<br>";
                        }
                    }
                    ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Kesimpulan -->
    <div class="section">
        <div class="section-title">KESIMPULAN DAN REKOMENDASI</div>
        
        <div class="kesimpulan">
            <h4>Kesimpulan Mutu Layanan</h4>
            <ul>
                <?php
                $avgIkm = $stats['summary']['rata_ikm'] ?? 0;
                $totalPeriods = $stats['summary']['total_periode'] ?? 0;
                $totalRespondents = $stats['summary']['total_responden'] ?? 0;
                
                // Determine overall category
                $overallCategory = '';
                if ($avgIkm >= 88.76) {
                    $overallCategory = 'Sangat Baik (A)';
                } elseif ($avgIkm >= 76.66) {
                    $overallCategory = 'Baik (B)';
                } elseif ($avgIkm >= 64.56) {
                    $overallCategory = 'Kurang Baik (C)';
                } else {
                    $overallCategory = 'Tidak Baik (D)';
                }
                ?>
                <li>
                    <strong>Rata-rata Nilai IKM:</strong> <?= number_format($avgIkm, 2) ?> 
                    dengan kategori <strong><?= $overallCategory ?></strong>
                </li>
                <li>
                    <strong>Total Periode Survei:</strong> <?= $totalPeriods ?> periode
                </li>
                <li>
                    <strong>Total Responden:</strong> <?= $totalRespondents ?> responden
                </li>
                <li>
                    <strong>Tren:</strong> 
                    <?php
                    if (count($rekapitulasi) >= 2) {
                        $firstIkm = end($rekapitulasi)['nilai_ikm'];
                        $lastIkm = reset($rekapitulasi)['nilai_ikm'];
                        $trend = $lastIkm - $firstIkm;
                        
                        if ($trend > 0) {
                            echo "Mengalami peningkatan sebesar " . number_format($trend, 2) . " poin";
                        } elseif ($trend < 0) {
                            echo "Mengalami penurunan sebesar " . number_format(abs($trend), 2) . " poin";
                        } else {
                            echo "Stabil tanpa perubahan signifikan";
                        }
                    } else {
                        echo "Belum ada data tren yang cukup";
                    }
                    ?>
                </li>
            </ul>
            
            <h4 style="margin-top: 15px;">Rekomendasi</h4>
            <ul>
                <?php if ($avgIkm < 76.66): ?>
                <li>Perlu dilakukan evaluasi menyeluruh terhadap kualitas pelayanan</li>
                <li>Peningkatan kompetensi SDM pelayanan perlu diprioritaskan</li>
                <li>Perbaikan infrastruktur dan fasilitas pelayanan disarankan</li>
                <?php elseif ($avgIkm < 88.76): ?>
                <li>Pertahankan kinerja pelayanan yang sudah baik</li>
                <li>Fokus pada peningkatan unsur-unsur dengan nilai terendah</li>
                <li>Lakukan benchmarking dengan unit berkinerja tinggi</li>
                <?php else: ?>
                <li>Pertahankan konsistensi pelayanan prima</li>
                <li>Jadikan unit percontohan untuk unit layanan lainnya</li>
                <li>Dokumentasikan best practices untuk replikasi</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh Sistem IKM</p>
        <p>Dicetak pada: <?= date('d F Y, H:i:s') ?> WIB</p>
        
        <div class="footer-signature">
            <div class="signature-block">
                <p>Mengetahui,</p>
                <p>Kepala Unit Layanan</p>
                <div class="signature-line">
                    <p>(___________________)</p>
                    <p>NIP. -</p>
                </div>
            </div>
            <div class="signature-block">
                <p>Yang Membuat,</p>
                <p>Operator IKM</p>
                <div class="signature-line">
                    <p>(___________________)</p>
                    <p>NIP. -</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
