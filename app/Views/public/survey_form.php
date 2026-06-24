<!DOCTYPE html>
<html lang="<?= $currentLocale ?? 'id' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- SEO Meta Tags -->
    <title><?= esc($seo['title'] ?? 'Survei IKM') ?></title>
    <meta name="description" content="<?= esc($seo['description'] ?? '') ?>">
    <meta name="keywords" content="<?= esc($seo['keywords'] ?? '') ?>">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        
        .survey-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
        }
        
        .survey-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            padding: 40px;
            margin-bottom: 30px;
        }
        
        .survey-header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .survey-header h1 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .survey-header .unit-name {
            color: #6c757d;
            font-size: 1.1rem;
        }
        
        .unsur-item {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid var(--primary-color);
        }
        
        .unsur-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .unsur-description {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        
        .rating-options {
            display: flex;
            gap: 10px;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        
        .rating-option {
            flex: 1;
            min-width: 60px;
        }
        
        .rating-option input[type="radio"] {
            display: none;
        }
        
        .rating-option label {
            display: block;
            text-align: center;
            padding: 12px 5px;
            background: white;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .rating-option input[type="radio"]:checked + label {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }
        
        .rating-option label:hover {
            border-color: var(--primary-color);
            background: #f0f4ff;
        }
        
        .rating-labels {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            font-size: 0.8rem;
            color: #6c757d;
        }
        
        .consent-section {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 10px;
            padding: 25px;
            margin: 30px 0;
        }
        
        .consent-section h4 {
            color: #856404;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .consent-info {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        
        .consent-info ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .consent-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
            padding: 10px;
            background: white;
            border-radius: 8px;
        }
        
        .consent-item input[type="checkbox"] {
            width: 20px;
            height: 20px;
            margin-right: 12px;
            margin-top: 2px;
            cursor: pointer;
        }
        
        .consent-item label {
            cursor: pointer;
            margin: 0;
            line-height: 1.5;
        }
        
        .consent-required {
            color: var(--danger-color);
            font-weight: 600;
        }
        
        .consent-optional {
            color: #6c757d;
            font-size: 0.85rem;
        }
        
        .demographic-section {
            background: #e7f3ff;
            border-radius: 10px;
            padding: 25px;
            margin: 30px 0;
        }
        
        .demographic-section h4 {
            color: #0c5460;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .form-section-title {
            color: var(--primary-color);
            font-weight: 600;
            margin: 30px 0 20px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .btn-submit {
            background: var(--primary-color);
            border: none;
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-submit:hover:not(:disabled) {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
        }
        
        .btn-submit:disabled {
            background: #6c757d;
            cursor: not-allowed;
            opacity: 0.7;
        }
        
        .loading-spinner {
            display: none;
        }
        
        .btn-submit.loading .loading-spinner {
            display: inline-block;
        }
        
        .btn-submit.loading .btn-text {
            display: none;
        }
        
        .required-mark {
            color: var(--danger-color);
        }
        
        @media (max-width: 768px) {
            .survey-card {
                padding: 20px;
            }
            
            .rating-options {
                gap: 5px;
            }
            
            .rating-option label {
                padding: 8px 3px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="survey-container">
        <?= form_open('survei/submit', ['id' => 'surveyForm', 'class' => 'survey-card']) ?>
            <!-- Header -->
            <div class="survey-header">
                <h1><i class="fas fa-clipboard-list"></i> Survei Kepuasan Masyarakat</h1>
                <p class="unit-name"><?= esc($unit['nama_unit'] ?? 'Unit Layanan') ?></p>
                <p class="text-muted mt-2">
                    Partisipasi Anda sangat berarti untuk peningkatan kualitas pelayanan publik.
                </p>
            </div>
            
            <!-- Hidden fields -->
            <input type="hidden" name="id_unit" value="<?= $id_unit ?>">
            <input type="hidden" name="id_periode" value="<?= $id_periode ?>">
            <?= csrf_field() ?>
            
            <!-- 9 Unsur Kuesioner -->
            <h4 class="form-section-title">
                <i class="fas fa-question-circle"></i> Penilaian Pelayanan
                <span class="required-mark">*</span>
            </h4>
            
            <?php foreach ($kuesionerList as $index => $kuesioner): ?>
            <div class="unsur-item">
                <div class="unsur-title">
                    <?= ($index + 1) ?>. <?= esc($kuesioner['nama_unsur']) ?>
                </div>
                <div class="unsur-description">
                    <?= esc($kuesioner['deskripsi'] ?? '') ?>
                </div>
                
                <div class="rating-options">
                    <?php for ($i = 1; $i <= 4; $i++): ?>
                    <div class="rating-option">
                        <input type="radio" 
                               name="jawaban[<?= $kuesioner['id_kuesioner'] ?>]" 
                               id="q<?= $kuesioner['id_kuesioner'] ?>_<?= $i ?>" 
                               value="<?= $i ?>"
                               required>
                        <label for="q<?= $kuesioner['id_kuesioner'] ?>_<?= $i ?>"><?= $i ?></label>
                    </div>
                    <?php endfor; ?>
                </div>
                <div class="rating-labels">
                    <span>Tidak Puas</span>
                    <span>Sangat Puas</span>
                </div>
            </div>
            <?php endforeach; ?>
            
            <!-- Data Demografis (Opsional) -->
            <h4 class="form-section-title">
                <i class="fas fa-user-friends"></i> Data Demografis <span class="text-muted">(Opsional)</span>
            </h4>
            
            <div class="demographic-section">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nama" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama" name="nama" placeholder="Nama Anda (opsional)">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="email@example.com">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="telepon" class="form-label">No. Telepon</label>
                        <input type="tel" class="form-control" id="telepon" name="telepon" placeholder="08xxxxxxxxxx">
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label for="usia_range" class="form-label">Rentang Usia</label>
                        <select class="form-select" id="usia_range" name="usia_range">
                            <option value="">Pilih...</option>
                            <option value="<17">&lt; 17 tahun</option>
                            <option value="17-25">17 - 25 tahun</option>
                            <option value="26-35">26 - 35 tahun</option>
                            <option value="36-45">36 - 45 tahun</option>
                            <option value="46-55">46 - 55 tahun</option>
                            <option value="56-65">56 - 65 tahun</option>
                            <option value=">65">&gt; 65 tahun</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label for="gender" class="form-label">Gender</label>
                        <select class="form-select" id="gender" name="gender">
                            <option value="">Pilih...</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Saran/Masukan (Opsional) -->
            <h4 class="form-section-title">
                <i class="fas fa-comment-dots"></i> Saran & Masukan <span class="text-muted">(Opsional)</span>
            </h4>
            
            <div class="mb-3">
                <textarea class="form-control" id="saran" name="saran" rows="4" placeholder="Tulis saran atau masukan Anda untuk perbaikan pelayanan..."></textarea>
            </div>
            
            <!-- Consent Management (UU PDP) -->
            <h4 class="form-section-title">
                <i class="fas fa-shield-alt"></i> Persetujuan Penggunaan Data
                <span class="required-mark">*</span>
            </h4>
            
            <div class="consent-section">
                <h4><i class="fas fa-info-circle"></i> Informasi Pengumpulan Data</h4>
                
                <div class="consent-info">
                    <strong>Tujuan Survei:</strong> Mengumpulkan feedback untuk meningkatkan kualitas pelayanan publik.<br><br>
                    <strong>Data yang Dikumpulkan:</strong> Jawaban survei, data demografis opsional, dan metadata teknis.<br><br>
                    <strong>Periode Penyimpanan:</strong> Data akan disimpan selama 2 (dua) tahun sesuai ketentuan UU PDP.
                </div>
                
                <div class="consent-item">
                    <input type="checkbox" id="consent_wajib" name="consent_wajib" required>
                    <label for="consent_wajib">
                        <strong class="consent-required">Saya setuju data saya digunakan untuk kalkulasi Indeks Kepuasan Masyarakat (IKM)</strong>
                        <span class="consent-optional d-block">* Wajib dicentang untuk melanjutkan</span>
                    </label>
                </div>
                
                <div class="consent-item">
                    <input type="checkbox" id="consent_followup" name="consent_followup">
                    <label for="consent_followup">
                        Saya setuju untuk dihubungi oleh petugas untuk tindak lanjut terkait survei ini
                        <span class="consent-optional d-block">(Opsional)</span>
                    </label>
                </div>
                
                <div class="consent-item">
                    <input type="checkbox" id="consent_publication" name="consent_publication">
                    <label for="consent_publication">
                        Saya setuju data saya dipublikasikan secara anonim untuk transparansi hasil survei
                        <span class="consent-optional d-block">(Opsional)</span>
                    </label>
                </div>
            </div>
            
            <!-- Submit Button -->
            <div class="text-center mt-5">
                <button type="submit" class="btn btn-primary btn-submit" id="btnSubmit" disabled>
                    <span class="spinner-border spinner-border-sm loading-spinner" role="status" aria-hidden="true"></span>
                    <span class="btn-text"><i class="fas fa-paper-plane"></i> Kirim Survei</span>
                </button>
            </div>
        <?= form_close() ?>
        
        <!-- Info Footer -->
        <div class="text-center text-muted mt-4">
            <small>
                <i class="fas fa-lock"></i> Data Anda aman dan dilindungi sesuai UU No. 27 Tahun 2022 tentang Perlindungan Data Pribadi
            </small>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.21.0/dist/jquery.validate.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
    $(document).ready(function() {
        // Initialize jQuery Validate
        $('#surveyForm').validate({
            rules: {
                'consent_wajib': {
                    required: true
                },
                'email': {
                    email: true
                },
                'jawaban': {
                    required: function() {
                        // Check if any jawaban is filled
                        let hasAnswer = false;
                        $('input[name^="jawaban["]').each(function() {
                            if ($(this).is(':checked')) {
                                hasAnswer = true;
                                return false;
                            }
                        });
                        return !hasAnswer;
                    }
                }
            },
            messages: {
                'consent_wajib': {
                    required: 'Anda harus menyetujui penggunaan data untuk melanjutkan.'
                },
                'email': {
                    email: 'Format email tidak valid.'
                }
            },
            errorClass: 'is-invalid',
            validClass: 'is-valid'
        });
        
        // Enable submit button when required consent is checked
        $('#consent_wajib').on('change', function() {
            const isChecked = $(this).is(':checked');
            $('#btnSubmit').prop('disabled', !isChecked);
            
            if (isChecked) {
                $('#btnSubmit').removeClass('btn-secondary').addClass('btn-primary');
            } else {
                $('#btnSubmit').removeClass('btn-primary').addClass('btn-secondary');
            }
        });
        
        // Form submission
        $('#surveyForm').on('submit', function(e) {
            e.preventDefault();
            
            // Check if form is valid
            if (!$(this).valid()) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    text: 'Silakan lengkapi semua field yang wajib diisi.',
                    confirmButtonColor: '#2563eb'
                });
                return false;
            }
            
            // Check if all 9 questions are answered
            let allAnswered = true;
            $('input[name^="jawaban["]').each(function() {
                const name = $(this).attr('name');
                if (!$(`input[name="${name}"]:checked`).length) {
                    allAnswered = false;
                    return false;
                }
            });
            
            if (!allAnswered) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Belum Lengkap',
                    text: 'Mohon jawab semua pertanyaan survei sebelum mengirim.',
                    confirmButtonColor: '#ffc107'
                });
                return false;
            }
            
            // Show loading state
            const btnSubmit = $('#btnSubmit');
            btnSubmit.addClass('loading').prop('disabled', true);
            
            // Submit via AJAX
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message || 'Terima kasih! Survei Anda telah berhasil disimpan.',
                            confirmButtonColor: '#28a745',
                            allowOutsideClick: false,
                            allowEscapeKey: false
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = response.redirect || '<?= site_url('survei/thank-you/' . $id_unit) ?>';
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message || 'Terjadi kesalahan. Silakan coba lagi.',
                            confirmButtonColor: '#dc3545'
                        });
                        btnSubmit.removeClass('loading').prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    let errorMsg = 'Terjadi kesalahan saat mengirim survei.';
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMsg,
                        confirmButtonColor: '#dc3545'
                    });
                    btnSubmit.removeClass('loading').prop('disabled', false);
                }
            });
            
            return false;
        });
        
        // Add visual feedback for answered questions
        $('input[name^="jawaban["]').on('change', function() {
            const unsurItem = $(this).closest('.unsur-item');
            if ($(this).is(':checked')) {
                unsurItem.addClass('border-success').css('border-left-color', '#28a745');
            }
        });
    });
    </script>
</body>
</html>
