<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Preview Kuesioner IKM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .survey-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .survey-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .survey-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .survey-header h1 {
            font-size: 1.8rem;
            margin-bottom: 10px;
        }
        .survey-header p {
            opacity: 0.9;
            margin: 0;
        }
        .unit-info {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
        }
        .unit-info h5 {
            color: #667eea;
            margin-bottom: 10px;
        }
        .survey-body {
            padding: 30px;
        }
        .question-item {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }
        .question-number {
            display: inline-block;
            background: #667eea;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            text-align: center;
            line-height: 30px;
            margin-right: 10px;
            font-weight: bold;
        }
        .question-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }
        .question-description {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        .likert-scale {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .likert-option {
            flex: 1;
            min-width: 60px;
        }
        .likert-option label {
            display: block;
            text-align: center;
            padding: 10px 5px;
            background: white;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.85rem;
        }
        .likert-option input[type="radio"] {
            display: none;
        }
        .likert-option input[type="radio"]:checked + span {
            display: block;
            font-weight: bold;
            color: #667eea;
        }
        .likert-option:hover {
            border-color: #667eea;
            background: #f0f0ff;
        }
        .likert-option input[type="radio"]:checked ~ label {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        .survey-footer {
            background: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 40px;
            font-size: 1.1rem;
            border-radius: 25px;
            color: white;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        .admin-preview-badge {
            position: fixed;
            top: 10px;
            right: 10px;
            background: #ffc107;
            color: #333;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: bold;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        .unsur-code-badge {
            background: #6c757d;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            margin-left: 8px;
        }
    </style>
</head>
<body>
    <!-- Admin Preview Badge -->
    <div class="admin-preview-badge">
        <i class="fas fa-eye me-1"></i> Mode Preview Admin
    </div>

    <div class="survey-container">
        <div class="survey-card">
            <!-- Header -->
            <div class="survey-header">
                <h1><i class="fas fa-clipboard-list me-2"></i>Survei Kepuasan Masyarakat</h1>
                <p>Indeks Kualitas Pelayanan Publik</p>
            </div>

            <!-- Unit Info -->
            <div class="unit-info">
                <h5><i class="fas fa-building me-2"></i><?= htmlspecialchars($unitInfo['nama_unit']) ?></h5>
                <p class="mb-1"><i class="fas fa-map-marker-alt me-2"></i><?= htmlspecialchars($unitInfo['alamat']) ?></p>
                <p class="mb-1"><i class="fas fa-phone me-2"></i><?= htmlspecialchars($unitInfo['telepon']) ?></p>
                <p class="mb-0"><i class="fas fa-envelope me-2"></i><?= htmlspecialchars($unitInfo['email']) ?></p>
            </div>

            <!-- Survey Body (Questions) -->
            <div class="survey-body">
                <div class="alert alert-info mb-4">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Petunjuk Pengisian:</strong> Berikan penilaian Anda dengan memilih salah satu opsi pada setiap pertanyaan.
                    Skala penilaian: 1 = Sangat Tidak Puas, 2 = Tidak Puas, 3 = Puas, 4 = Sangat Puas.
                </div>

                <?php if(empty($kuesionerList)): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Belum ada kuesioner aktif yang tersedia.
                    </div>
                <?php else: ?>
                    <form id="surveyForm">
                        <?php $no = 1; foreach($kuesionerList as $k): ?>
                            <div class="question-item">
                                <div class="question-title">
                                    <span class="question-number"><?= $no++ ?></span>
                                    <?= htmlspecialchars($k['nama_unsur']) ?>
                                    <span class="unsur-code-badge"><?= htmlspecialchars($k['unsur_code']) ?></span>
                                </div>
                                
                                <?php if(!empty($k['deskripsi'])): ?>
                                    <div class="question-description">
                                        <i class="fas fa-lightbulb me-1"></i> <?= htmlspecialchars($k['deskripsi']) ?>
                                    </div>
                                <?php endif; ?>

                                <div class="likert-scale">
                                    <div class="likert-option">
                                        <input type="radio" name="jawaban[<?= $k['id_kuesioner'] ?>]" value="1" id="q<?= $k['id_kuesioner'] ?>_1">
                                        <label for="q<?= $k['id_kuesioner'] ?>_1">
                                            <span>1</span><br><small>Sangat Tidak Puas</small>
                                        </label>
                                    </div>
                                    <div class="likert-option">
                                        <input type="radio" name="jawaban[<?= $k['id_kuesioner'] ?>]" value="2" id="q<?= $k['id_kuesioner'] ?>_2">
                                        <label for="q<?= $k['id_kuesioner'] ?>_2">
                                            <span>2</span><br><small>Tidak Puas</small>
                                        </label>
                                    </div>
                                    <div class="likert-option">
                                        <input type="radio" name="jawaban[<?= $k['id_kuesioner'] ?>]" value="3" id="q<?= $k['id_kuesioner'] ?>_3">
                                        <label for="q<?= $k['id_kuesioner'] ?>_3">
                                            <span>3</span><br><small>Puas</small>
                                        </label>
                                    </div>
                                    <div class="likert-option">
                                        <input type="radio" name="jawaban[<?= $k['id_kuesioner'] ?>]" value="4" id="q<?= $k['id_kuesioner'] ?>_4">
                                        <label for="q<?= $k['id_kuesioner'] ?>_4">
                                            <span>4</span><br><small>Sangat Puas</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- Saran Section -->
                        <div class="question-item" style="border-left-color: #28a745;">
                            <div class="question-title">
                                <span class="question-number" style="background: #28a745;"><i class="fas fa-comment"></i></span>
                                Saran dan Masukan
                            </div>
                            <div class="question-description">
                                Berikan saran atau masukan untuk meningkatkan kualitas pelayanan kami
                            </div>
                            <textarea name="saran" class="form-control" rows="4" placeholder="Tulis saran Anda di sini..."></textarea>
                        </div>
                    </form>
                <?php endif; ?>
            </div>

            <!-- Footer -->
            <div class="survey-footer">
                <button type="submit" form="surveyForm" class="btn btn-submit" disabled>
                    <i class="fas fa-paper-plane me-2"></i>Kirim Penilaian
                </button>
                <p class="mt-3 mb-0 text-muted small">
                    <i class="fas fa-shield-alt me-1"></i>
                    Data Anda dilindungi dan dirahasiakan sesuai UU PDP
                </p>
            </div>
        </div>

        <!-- Back to Admin Link -->
        <div class="text-center mt-4">
            <a href="/kuesioner" class="btn btn-outline-light">
                <i class="fas fa-arrow-left me-1"></i>Kembali ke Manajemen Kuesioner
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Enable submit button when all questions are answered
        document.querySelectorAll('input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const form = document.getElementById('surveyForm');
                const totalQuestions = form.querySelectorAll('.question-item').length - 1; // Exclude saran section
                const answeredQuestions = form.querySelectorAll('input[type="radio"]:checked').length;
                
                const submitBtn = form.querySelector('button[type="submit"]');
                if (answeredQuestions >= totalQuestions) {
                    submitBtn.disabled = false;
                } else {
                    submitBtn.disabled = true;
                }
            });
        });

        // Form submission handler (preview only - no actual submission)
        document.getElementById('surveyForm').addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                icon: 'info',
                title: 'Mode Preview',
                text: 'Ini adalah mode preview admin. Pada halaman survei publik yang sebenarnya, data akan dikirim ke server.',
                confirmButtonText: 'OK'
            });
        });
    </script>
</body>
</html>
