<?php

namespace App\Controllers\PublicController;

use App\Controllers\BaseController;
use App\Models\SurveiModel;
use App\Models\RespondenModel;
use App\Models\ConsentModel;
use App\Jobs\KalkulasiIKMJob;
use CodeIgniter\API\ResponseTrait;

/**
 * SurveiController
 * 
 * Controller publik untuk halaman survei IKM
 * Tanpa autentikasi - dapat diakses oleh masyarakat umum
 * 
 * Berdasarkan SRS F-05, UC-06, dan UC-23 (UU PDP Consent)
 */
class SurveiController extends BaseController
{
    use ResponseTrait;
    
    protected SurveiModel $surveiModel;
    protected RespondenModel $respondenModel;
    protected ConsentModel $consentModel;
    protected KalkulasiIKMJob $ikmJob;
    
    public function __construct()
    {
        $this->surveiModel = new SurveiModel();
        $this->respondenModel = new RespondenModel();
        $this->consentModel = new ConsentModel();
        $this->ikmJob = new KalkulasiIKMJob();
        
        // Force HTTPS for production
        if (ENVIRONMENT === 'production' && !$this->request->isSecure()) {
            return redirect()->to(base_url(uri_string()), 'location', 301);
        }
    }
    
    /**
     * Display survey form
     * GET /survei or /survei/index/{id_unit}/{id_periode}
     */
    public function index(?int $idUnit = null, ?int $idPeriode = null)
    {
        // Get unit and periode from URL or session
        $idUnit = $idUnit ?? session('survey_unit_id');
        $idPeriode = $idPeriode ?? session('survey_periode_id');
        
        // If not provided, get active period and default unit
        if (!$idUnit || !$idPeriode) {
            $db = \Config\Database::connect();
            
            // Get first active unit
            $unitRow = $db->table('tb_unit_layanan')
                ->where('is_active', 1)
                ->orderBy('id_unit', 'ASC')
                ->get()
                ->getRowArray();
            
            if (!$unitRow) {
                return redirect()->to('/')->with('error', 'Tidak ada unit layanan aktif.');
            }
            
            $idUnit = $unitRow['id_unit'];
            
            // Get active period
            $periodeRow = $db->table('tb_periode')
                ->where('is_active', 1)
                ->where('is_locked', 0)
                ->orderBy('tahun', 'DESC')
                ->orderBy('bulan_mulai', 'DESC')
                ->get()
                ->getRowArray();
            
            if (!$periodeRow) {
                return redirect()->to('/')->with('error', 'Tidak ada periode survei aktif.');
            }
            
            $idPeriode = $periodeRow['id_periode'];
        }
        
        // Store in session for convenience
        session(['survey_unit_id' => $idUnit, 'survey_periode_id' => $idPeriode]);
        
        // Get unit info
        $unitModel = new \App\Models\UnitLayananModel();
        $unit = $unitModel->find($idUnit);
        
        if (!$unit) {
            return redirect()->to('/')->with('error', 'Unit layanan tidak ditemukan.');
        }
        
        // Check if already submitted (anti-duplication)
        $fingerprint = $this->generateFingerprint();
        $alreadySubmitted = $this->surveiModel->checkDuplicate($fingerprint, $idUnit, $idPeriode);
        
        if ($alreadySubmitted) {
            return view('public/survey_already_submitted', [
                'unit' => $unit,
                'currentLocale' => get_current_locale()
            ]);
        }
        
        // Get 9 unsur kuesioner
        $kuesionerList = $this->surveiModel->getUnsurWajib();
        
        // Prepare SEO data
        $seoData = [
            'title' => 'Survei Kepuasan Masyarakat - ' . $unit['nama_unit'],
            'description' => 'Partisipasi Anda dalam survei ini sangat berarti untuk peningkatan kualitas pelayanan publik.',
            'keywords' => 'survei, IKM, kepuasan masyarakat, pelayanan publik',
            'canonical' => current_url(),
        ];
        
        $data = [
            'unit' => $unit,
            'id_unit' => $idUnit,
            'id_periode' => $idPeriode,
            'kuesionerList' => $kuesionerList,
            'fingerprint' => $fingerprint,
            'seo' => $seoData,
            'currentLocale' => get_current_locale(),
            'supportedLocales' => ['id' => 'Indonesia', 'en' => 'English']
        ];
        
        return view('public/survey_form', $data);
    }
    
    /**
     * Submit survey response
     * POST /survei/submit
     */
    public function submit()
    {
        // Verify CSRF token
        if (!$this->validate([
            'csrf_test_name' => 'required'
        ])) {
            return $this->fail('Token keamanan tidak valid.', 400);
        }
        
        // Validate required fields
        $validation = \Config\Services::validation();
        $validation->setRules([
            'id_unit' => 'required|integer',
            'id_periode' => 'required|integer',
            'usia_range' => 'permit_empty|in_list[<17,17-25,26-35,36-45,46-55,56-65,>65]',
            'gender' => 'permit_empty|in_list[L,P]',
            'email' => 'permit_empty|valid_email',
            'telepon' => 'permit_empty|max_length[50]',
            'consent_wajib' => 'required',
        ]);
        
        if (!$validation->withRequest($this->request)->run()) {
            return $this->failValidationErrors($validation->getErrors());
        }
        
        $db = \Config\Database::connect();
        
        try {
            $db->transStart();
            
            // Get form data
            $idUnit = $this->request->getPost('id_unit');
            $idPeriode = $this->request->getPost('id_periode');
            $answers = $this->request->getPost('jawaban'); // Array of [id_kuesioner => nilai]
            
            // Check duplicate submission
            $fingerprint = $this->generateFingerprint();
            if ($this->surveiModel->checkDuplicate($fingerprint, $idUnit, $idPeriode)) {
                $db->transRollback();
                return $this->fail('Anda sudah mengisi survei ini.', 400);
            }
            
            // Create respondent record
            $respondenData = [
                'id_unit' => $idUnit,
                'id_periode' => $idPeriode,
                'nama' => $this->request->getPost('nama'), // Optional
                'email' => $this->request->getPost('email'), // Optional
                'telepon' => $this->request->getPost('telepon'), // Optional
                'jenis_kelamin' => $this->request->getPost('gender'),
                'usia' => $this->calculateUsia($this->request->getPost('usia_range')),
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent(),
                'consent_given' => 1, // Required consent checked
            ];
            
            $idResponden = $this->respondenModel->createRespondent($respondenData);
            
            if (!$idResponden) {
                throw new \Exception('Gagal menyimpan data responden.');
            }
            
            // Save answers (9 unsur)
            $jawabanData = [];
            foreach ($answers as $idKuesioner => $nilai) {
                if ($nilai >= 1 && $nilai <= 4) {
                    $jawabanData[] = [
                        'id_responden' => $idResponden,
                        'id_kuesioner' => $idKuesioner,
                        'id_periode' => $idPeriode,
                        'id_unit' => $idUnit,
                        'nilai' => (int)$nilai,
                        'created_at' => date('Y-m-d H:i:s'),
                    ];
                }
            }
            
            if (empty($jawabanData)) {
                throw new \Exception('Tidak ada jawaban survei.');
            }
            
            $this->surveiModel->saveAnswers($jawabanData);
            
            // Log consents (UU PDP compliance)
            $context = [
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent(),
                'device_fingerprint' => $fingerprint,
            ];
            
            // Required consent: survey participation
            $this->consentModel->logConsent(
                $idResponden,
                ConsentModel::CONSENT_SURVEY,
                true,
                $context
            );
            
            // Optional consent: follow-up contact
            $consentFollowup = $this->request->getPost('consent_followup');
            $this->consentModel->logConsent(
                $idResponden,
                ConsentModel::CONSENT_FOLLOWUP,
                !empty($consentFollowup),
                $context
            );
            
            // Optional consent: anonymous publication
            $consentPublication = $this->request->getPost('consent_publication');
            $this->consentModel->logConsent(
                $idResponden,
                ConsentModel::CONSENT_PUBLICATION,
                !empty($consentPublication),
                $context
            );
            
            // Save optional suggestion/feedback
            $saran = $this->request->getPost('saran');
            if (!empty($saran)) {
                $db->table('tb_saran')->insert([
                    'id_responden' => $idResponden,
                    'id_unit' => $idUnit,
                    'id_periode' => $idPeriode,
                    'isi_saran' => $saran,
                    'status_tindak_lanjut' => 'pending',
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
            
            $db->transCommit();
            
            // Dispatch IKM calculation job to Redis queue
            $this->ikmJob->dispatch([
                'id_unit' => $idUnit,
                'id_periode' => $idPeriode,
                'id_responden' => $idResponden,
                'trigger' => 'survey_submission'
            ]);
            
            // Return success response
            return $this->respond([
                'success' => true,
                'message' => 'Terima kasih! Survei Anda telah berhasil disimpan.',
                'redirect' => site_url('survei/thank-you/' . $idUnit)
            ], 200);
            
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', '[SurveiController] Submit error: ' . $e->getMessage());
            return $this->fail('Terjadi kesalahan saat menyimpan survei. Silakan coba lagi.', 500);
        }
    }
    
    /**
     * Thank you page after submission
     */
    public function thankYou(?int $idUnit = null)
    {
        $idUnit = $idUnit ?? session('survey_unit_id');
        
        $unitModel = new \App\Models\UnitLayananModel();
        $unit = $unitModel->find($idUnit);
        
        $data = [
            'unit' => $unit,
            'currentLocale' => get_current_locale()
        ];
        
        return view('public/survey_thank_you', $data);
    }
    
    /**
     * Generate device fingerprint for anti-duplication
     */
    protected function generateFingerprint(): string
    {
        $ipAddress = $this->request->getIPAddress();
        $userAgent = $this->request->getUserAgent();
        $timestamp = date('Y-m-d H'); // Hour-based to allow multiple per day
        
        return hash('sha256', "{$ipAddress}|{$userAgent}|{$timestamp}");
    }
    
    /**
     * Calculate numeric usia from range
     */
    protected function calculateUsia(?string $range): ?int
    {
        if (!$range) return null;
        
        $mapping = [
            '<17' => 16,
            '17-25' => 21,
            '26-35' => 30,
            '36-45' => 40,
            '46-55' => 50,
            '56-65' => 60,
            '>65' => 66,
        ];
        
        return $mapping[$range] ?? null;
    }
    
    /**
     * Helper method for validation errors
     */
    protected function failValidationErrors(array $errors)
    {
        return $this->fail([
            'validation_errors' => $errors,
            'message' => 'Validasi gagal. Silakan periksa form Anda.'
        ], 400);
    }
}
