<?php

namespace App\Jobs;

use Predis\Client as RedisClient;
use Exception;

/**
 * KalkulasiIKMJob
 * 
 * Job untuk menghitung Indeks Kepuasan Masyarakat (IKM)
 * yang masuk ke Redis queue setelah survei submitted.
 * 
 * Berdasarkan SRS F-05 dan UC-23
 */
class KalkulasiIKMJob
{
    protected RedisClient $redis;
    protected const QUEUE_NAME = 'ikm-calculation';
    
    public function __construct()
    {
        $config = config('Redis');
        $this->redis = new RedisClient([
            'scheme' => $config->scheme ?? 'tcp',
            'host' => $config->host ?? '127.0.0.1',
            'port' => $config->port ?? 6379,
            'password' => $config->password ?? null,
            'database' => $config->database ?? 0,
        ]);
    }
    
    /**
     * Dispatch job to Redis queue
     * 
     * @param array $payload Data untuk kalkulasi IKM
     * @return bool True jika berhasil dispatch
     */
    public function dispatch(array $payload): bool
    {
        try {
            $jobData = [
                'job_id' => uniqid('ikm_', true),
                'queue' => self::QUEUE_NAME,
                'payload' => $payload,
                'created_at' => date('Y-m-d H:i:s'),
                'status' => 'pending'
            ];
            
            // Serialize to JSON and push to Redis list (LPUSH for LIFO, RPUSH for FIFO)
            $this->redis->rpush(
                self::QUEUE_NAME,
                json_encode($jobData, JSON_UNESCAPED_UNICODE)
            );
            
            // Also persist to database as fallback
            $this->persistToDatabase($jobData);
            
            return true;
        } catch (Exception $e) {
            log_message('error', '[KalkulasiIKMJob] Failed to dispatch: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Persist job to database as fallback (UU PDP audit trail)
     */
    protected function persistToDatabase(array $jobData): void
    {
        $db = \Config\Database::connect();
        
        $data = [
            'queue_name' => self::QUEUE_NAME,
            'job_class' => self::class,
            'payload' => json_encode($jobData['payload'], JSON_UNESCAPED_UNICODE),
            'status' => 'pending',
            'attempts' => 0,
            'max_attempts' => 3,
            'available_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
        ];
        
        $db->table('tb_queue_jobs')->insert($data);
    }
    
    /**
     * Pop job from queue for processing
     * 
     * @return array|null Job data or null if queue empty
     */
    public function pop(): ?array
    {
        try {
            $jobData = $this->redis->lpop(self::QUEUE_NAME);
            
            if ($jobData) {
                return json_decode($jobData, true);
            }
            
            return null;
        } catch (Exception $e) {
            log_message('error', '[KalkulasiIKMJob] Failed to pop: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Process the IKM calculation
     * 
     * Formula IKM berdasarkan Peraturan Menteri PANRB:
     * - Nilai per unsur: rata-rata dari semua jawaban (skala 1-4)
     * - Nilai IKM = (Total nilai rata-rata / Jumlah unsur) x 25
     * - Konversi ke skala 0-100
     * 
     * @param array $payload Job payload
     * @return array Hasil kalkulasi
     */
    public function process(array $payload): array
    {
        $idUnit = $payload['id_unit'] ?? 0;
        $idPeriode = $payload['id_periode'] ?? 0;
        
        if (!$idUnit || !$idPeriode) {
            throw new Exception('Invalid payload: missing id_unit or id_periode');
        }
        
        $db = \Config\Database::connect();
        
        // Get all answers for this unit and period
        $answersQuery = $db->table('tb_survei_jawaban sj')
            ->select('sj.id_kuesioner, sj.nilai, k.unsur_code, k.bobot')
            ->join('tb_kuesioner k', 'k.id_kuesioner = sj.id_kuesioner')
            ->where('sj.id_unit', $idUnit)
            ->where('sj.id_periode', $idPeriode)
            ->get()
            ->getResultArray();
        
        if (empty($answersQuery)) {
            return ['status' => 'no_data', 'message' => 'Tidak ada data jawaban'];
        }
        
        // Calculate average per unsur
        $unsurScores = [];
        foreach ($answersQuery as $row) {
            $code = $row['unsur_code'];
            if (!isset($unsurScores[$code])) {
                $unsurScores[$code] = [
                    'total' => 0,
                    'count' => 0,
                    'bobot' => $row['bobot'] ?? 1.0
                ];
            }
            $unsurScores[$code]['total'] += $row['nilai'];
            $unsurScores[$code]['count']++;
        }
        
        // Calculate average and weighted score
        $totalWeightedScore = 0;
        $totalBobot = 0;
        $unsurResults = [];
        
        foreach ($unsurScores as $code => $data) {
            $average = $data['total'] / $data['count'];
            $weightedScore = $average * $data['bobot'];
            
            $unsurResults[] = [
                'unsur_code' => $code,
                'average' => round($average, 2),
                'bobot' => $data['bobot'],
                'weighted_score' => round($weightedScore, 2),
                'response_count' => $data['count']
            ];
            
            $totalWeightedScore += $weightedScore;
            $totalBobot += $data['bobot'];
        }
        
        // Calculate final IKM score (convert to 0-100 scale)
        // Formula: (Total Weighted Score / Total Bobot) x 25
        $ikmRaw = ($totalWeightedScore / $totalBobot);
        $ikmFinal = $ikmRaw * 25;
        
        // Determine quality category (Peraturan Menteri PANRB)
        $category = $this->getQualityCategory($ikmFinal);
        
        $result = [
            'status' => 'success',
            'id_unit' => $idUnit,
            'id_periode' => $idPeriode,
            'ikm_raw' => round($ikmRaw, 4),
            'ikm_final' => round($ikmFinal, 2),
            'category' => $category,
            'unsur_details' => $unsurResults,
            'total_responses' => count(array_unique(array_column($answersQuery, 'id_responden'))),
            'calculated_at' => date('Y-m-d H:i:s')
        ];
        
        // Save result to tb_rekap_ikm
        $this->saveRekapIKM($result);
        
        return $result;
    }
    
    /**
     * Get quality category based on IKM score
     * Permenpan RB No. 14 Tahun 2017
     */
    protected function getQualityCategory(float $score): array
    {
        if ($score >= 88.76 && $score <= 100) {
            return ['code' => 'A', 'label' => 'Sangat Baik', 'color' => '#28a745'];
        } elseif ($score >= 76.66 && $score < 88.76) {
            return ['code' => 'B', 'label' => 'Baik', 'color' => '#17a2b8'];
        } elseif ($score >= 64.56 && $score < 76.66) {
            return ['code' => 'C', 'label' => 'Kurang Baik', 'color' => '#ffc107'];
        } else {
            return ['code' => 'D', 'label' => 'Tidak Baik', 'color' => '#dc3545'];
        }
    }
    
    /**
     * Save IKM recap to database
     */
    protected function saveRekapIKM(array $result): void
    {
        $db = \Config\Database::connect();
        
        $data = [
            'id_unit' => $result['id_unit'],
            'id_periode' => $result['id_periode'],
            'nilai_ikm' => $result['ikm_final'],
            'kategori' => $result['category']['code'],
            'predikat' => $result['category']['label'],
            'jumlah_responden' => $result['total_responses'],
            'detail_unsur' => json_encode($result['unsur_details'], JSON_UNESCAPED_UNICODE),
            'created_at' => date('Y-m-d H:i:s'),
        ];
        
        $db->table('tb_rekap_ikm')->insert($data);
    }
    
    /**
     * Get queue size
     */
    public function getQueueSize(): int
    {
        try {
            return (int)$this->redis->llen(self::QUEUE_NAME);
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Clear queue (for maintenance)
     */
    public function clearQueue(): void
    {
        try {
            $this->redis->del(self::QUEUE_NAME);
        } catch (Exception $e) {
            log_message('error', '[KalkulasiIKMJob] Failed to clear queue: ' . $e->getMessage());
        }
    }
}
