<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<style>
@media print {
    body * {
        visibility: hidden;
    }
    #printArea, #printArea * {
        visibility: visible;
    }
    #printArea {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0;
        padding: 20px;
    }
    .no-print {
        display: none !important;
    }
}

.print-layout {
    max-width: 800px;
    margin: 0 auto;
    padding: 40px;
    border: 2px solid #333;
    background: white;
}

.print-header {
    text-align: center;
    margin-bottom: 30px;
    border-bottom: 2px solid #333;
    padding-bottom: 20px;
}

.print-logo {
    max-width: 150px;
    height: auto;
    margin-bottom: 15px;
}

.print-title {
    font-size: 24px;
    font-weight: bold;
    margin: 10px 0;
}

.print-subtitle {
    font-size: 16px;
    color: #666;
}

.qr-section {
    text-align: center;
    margin: 40px 0;
}

.qr-image {
    max-width: 300px;
    height: auto;
    border: 2px solid #ddd;
    padding: 10px;
    margin: 0 auto;
}

.instruction-section {
    margin-top: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.instruction-title {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 10px;
}

.instruction-steps {
    text-align: left;
    list-style: decimal;
    padding-left: 20px;
}

.instruction-steps li {
    margin: 8px 0;
}

.scan-hint {
    font-size: 14px;
    color: #666;
    margin-top: 15px;
    font-style: italic;
}

.footer-info {
    margin-top: 40px;
    padding-top: 20px;
    border-top: 1px solid #ddd;
    font-size: 12px;
    color: #666;
    text-align: center;
}
</style>

<div class="container-fluid no-print">
    <div class="row mb-3">
        <div class="col-12">
            <button class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Cetak Halaman
            </button>
            <a href="<?= site_url('admin/qr/list') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <a href="<?= site_url('admin/qr/download/' . $qr['id_qr']) ?>" class="btn btn-success">
                <i class="fas fa-download"></i> Unduh File QR
            </a>
        </div>
    </div>
</div>

<div id="printArea" class="print-layout">
    <div class="print-header">
        <?php if (!empty($qr['logo_path']) && file_exists($qr['logo_path'])): ?>
            <img src="<?= base_url($qr['logo_path']) ?>" alt="Logo" class="print-logo">
        <?php endif; ?>
        <h1 class="print-title"><?= esc($qr['nama_unit']) ?></h1>
        <p class="print-subtitle">Survei Kepuasan Masyarakat (IKM)</p>
        <?php if (!empty($qr['nama_periode'])): ?>
            <p class="print-subtitle">Periode: <?= esc($qr['nama_periode']) ?></p>
        <?php endif; ?>
    </div>
    
    <div class="qr-section">
        <?php 
        $filePath = WRITEPATH . $qr['file_path'];
        $imageData = '';
        if (file_exists($filePath)) {
            $imageData = base64_encode(file_get_contents($filePath));
            $mimeType = mime_content_type($filePath);
        }
        ?>
        <?php if ($imageData): ?>
            <img src="data:<?= $mimeType ?>;base64,<?= $imageData ?>" alt="QR Code" class="qr-image">
        <?php else: ?>
            <div class="alert alert-warning">File QR Code tidak ditemukan</div>
        <?php endif; ?>
        
        <p class="scan-hint">
            <i class="fas fa-qrcode"></i> 
            Scan QR Code di atas dengan kamera smartphone Anda untuk mengakses survei
        </p>
    </div>
    
    <div class="instruction-section">
        <h3 class="instruction-title">Cara Mengisi Survei:</h3>
        <ol class="instruction-steps">
            <li>Buka kamera smartphone atau aplikasi QR Code scanner</li>
            <li>Arahkan kamera ke QR Code di atas</li>
            <li>Klik link yang muncul untuk membuka halaman survei</li>
            <li>Isi kuesioner dengan memberikan penilaian Anda</li>
            <li>Kirim jawaban Anda</li>
        </ol>
    </div>
    
    <div class="footer-info">
        <p><strong>URL Akses Langsung:</strong></p>
        <code><?= site_url('q/' . $qr['short_url']) ?></code>
        <p style="margin-top: 10px;">
            Terima kasih atas partisipasi Anda dalam meningkatkan kualitas pelayanan publik.
        </p>
        <p style="margin-top: 15px; font-size: 10px;">
            Dicetak pada: <?= date('d/m/Y H:i:s') ?> | ID QR: <?= $qr['id_qr'] ?>
        </p>
    </div>
</div>
<?= $this->endSection() ?>
