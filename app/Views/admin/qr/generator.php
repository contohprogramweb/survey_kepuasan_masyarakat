<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-qrcode"></i> <?= esc($title) ?></h3>
                </div>
                <div class="card-body">
                    <form id="qrForm" method="POST" action="<?= site_url('admin/qr/save') ?>">
                        <?= csrf_field() ?>
                        
                        <div class="row">
                            <!-- Left Column: Form Inputs -->
                            <div class="col-md-6">
                                <!-- Unit Layanan -->
                                <div class="form-group mb-3">
                                    <label for="id_unit" class="form-label">Unit Layanan <span class="text-danger">*</span></label>
                                    <select class="form-select" id="id_unit" name="id_unit" required>
                                        <option value="">-- Pilih Unit Layanan --</option>
                                        <?php foreach ($units as $unit): ?>
                                            <option value="<?= $unit['id_unit'] ?>"><?= esc($unit['nama_unit']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <!-- Periode Survei -->
                                <div class="form-group mb-3">
                                    <label for="id_periode" class="form-label">Periode Survei</label>
                                    <select class="form-select" id="id_periode" name="id_periode">
                                        <option value="">-- Pilih Periode (Opsional) --</option>
                                        <?php foreach ($periods as $periode): ?>
                                            <option value="<?= $periode['id_periode'] ?>"><?= esc($periode['nama_periode']) ?> (<?= $periode['tahun'] ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" id="periode_name" name="periode_name" value="">
                                </div>
                                
                                <!-- Format Output -->
                                <div class="form-group mb-3">
                                    <label for="format" class="form-label">Format Output <span class="text-danger">*</span></label>
                                    <select class="form-select" id="format" name="format" required>
                                        <?php foreach ($formats as $value => $label): ?>
                                            <option value="<?= $value ?>" <?= $value === 'png' ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <!-- Ukuran Preset -->
                                <div class="form-group mb-3">
                                    <label for="size_preset" class="form-label">Ukuran QR Code</label>
                                    <select class="form-select" id="size_preset" name="size_preset">
                                        <?php foreach ($sizePresets as $value => $label): ?>
                                            <option value="<?= $value ?>" <?= $value === 'M' ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <!-- Short URL Custom -->
                                <div class="form-group mb-3">
                                    <label for="short_url" class="form-label">Short URL (Kode Unik) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><?= base_url('q/') ?></span>
                                        <input type="text" class="form-control" id="short_url" name="short_url" 
                                               placeholder="contoh: ikm-2024-q1" required 
                                               pattern="[a-zA-Z0-9\-_]+" 
                                               maxlength="50">
                                    </div>
                                    <small class="form-text text-muted">Gunakan huruf, angka, dash (-), atau underscore (_)</small>
                                </div>
                                
                                <!-- Logo Instansi -->
                                <div class="form-group mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="include_logo" name="include_logo" value="1">
                                        <label class="form-check-label" for="include_logo">
                                            Sertakan Logo Instansi di Tengah QR Code
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="form-group mb-3">
                                    <button type="button" class="btn btn-info" id="btnPreview">
                                        <i class="fas fa-eye"></i> Preview
                                    </button>
                                    <button type="submit" class="btn btn-primary" id="btnSave">
                                        <i class="fas fa-save"></i> Simpan QR Code
                                    </button>
                                    <button type="button" class="btn btn-secondary" id="btnReset">
                                        <i class="fas fa-redo"></i> Reset
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Right Column: Preview -->
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Preview QR Code</h5>
                                    </div>
                                    <div class="card-body text-center">
                                        <div id="previewContainer" style="min-height: 300px; display: flex; align-items: center; justify-content: center;">
                                            <p class="text-muted">Klik tombol "Preview" untuk melihat pratinjau QR Code</p>
                                        </div>
                                        <div id="urlInfo" class="mt-3" style="display: none;">
                                            <hr>
                                            <p class="mb-1"><strong>Tracking URL:</strong></p>
                                            <code id="trackingUrl" class="d-block text-break small"></code>
                                            <p class="mb-1 mt-2"><strong>Landing URL:</strong></p>
                                            <code id="landingUrl" class="d-block text-break small"></code>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Download Options -->
<div class="modal fade" id="downloadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">QR Code Berhasil Dibuat!</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p>QR Code telah berhasil disimpan.</p>
                <div class="d-grid gap-2">
                    <a href="#" id="downloadLink" class="btn btn-success">
                        <i class="fas fa-download"></i> Unduh File
                    </a>
                    <a href="#" id="printLink" class="btn btn-info">
                        <i class="fas fa-print"></i> Cetak Layout
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('qrForm');
    const btnPreview = document.getElementById('btnPreview');
    const btnSave = document.getElementById('btnSave');
    const btnReset = document.getElementById('btnReset');
    const previewContainer = document.getElementById('previewContainer');
    const urlInfo = document.getElementById('urlInfo');
    
    // Auto-generate short URL from unit and periode selection
    document.getElementById('id_unit').addEventListener('change', generateShortUrl);
    document.getElementById('id_periode').addEventListener('change', function() {
        generateShortUrl();
        // Set periode name hidden field
        const selectedOption = this.options[this.selectedIndex];
        document.getElementById('periode_name').value = selectedOption.text.split('(')[0].trim();
    });
    
    function generateShortUrl() {
        const idUnit = document.getElementById('id_unit').value;
        const idPeriode = document.getElementById('id_periode').value;
        if (idUnit) {
            const timestamp = new Date().getTime().toString(36);
            const suggestedCode = `ikm-${idUnit}-${idPeriode || 'active'}-${timestamp}`;
            document.getElementById('short_url').value = suggestedCode.toLowerCase();
        }
    }
    
    // Preview button handler
    btnPreview.addEventListener('click', function() {
        const formData = new FormData(form);
        formData.append('preview', '1');
        
        previewContainer.innerHTML = '<div class="spinner-border text-primary" role="status"></div>';
        
        fetch('<?= site_url('admin/qr/preview') ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.preview_url.includes('image/svg')) {
                    previewContainer.innerHTML = `<img src="${data.preview_url}" alt="QR Preview" style="max-width: 100%; height: auto;">`;
                } else {
                    previewContainer.innerHTML = `<img src="${data.preview_url}" alt="QR Preview" style="max-width: 300px;">`;
                }
                
                document.getElementById('trackingUrl').textContent = data.tracking_url;
                document.getElementById('landingUrl').textContent = data.landing_url;
                urlInfo.style.display = 'block';
            } else {
                previewContainer.innerHTML = `<div class="alert alert-danger">${data.message || 'Gagal generate preview'}</div>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            previewContainer.innerHTML = '<div class="alert alert-danger">Terjadi kesalahan saat generate preview</div>';
        });
    });
    
    // Form submit handler
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        
        btnSave.disabled = true;
        btnSave.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            btnSave.disabled = false;
            btnSave.innerHTML = '<i class="fas fa-save"></i> Simpan QR Code';
            
            if (data.success) {
                document.getElementById('downloadLink').href = data.download_url;
                document.getElementById('printLink').href = data.print_url;
                const modal = new bootstrap.Modal(document.getElementById('downloadModal'));
                modal.show();
            } else {
                alert(data.message || 'Gagal menyimpan QR Code');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            btnSave.disabled = false;
            btnSave.innerHTML = '<i class="fas fa-save"></i> Simpan QR Code';
            alert('Terjadi kesalahan saat menyimpan QR Code');
        });
    });
    
    // Reset button
    btnReset.addEventListener('click', function() {
        form.reset();
        previewContainer.innerHTML = '<p class="text-muted">Klik tombol "Preview" untuk melihat pratinjau QR Code</p>';
        urlInfo.style.display = 'none';
    });
});
</script>
<?= $this->endSection() ?>
