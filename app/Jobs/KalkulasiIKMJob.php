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
 * Berdasarkan SRS F-05, F-06, F-24, dan bagian 7 (Algoritma Kalkulasi IKM)
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
     * - NRR (Nilai Rata-Rata) per unsur
     * - NRR Tertimbang = NRR × Bobot (0.111 untuk setiap unsur)
     * - IKM Total = Σ NRR_t × 25 (konversi ke skala 0-100)
     * - Hitung delta vs periode sebelumnya
     * - Set flag_alert jika penurunan > threshold (default 5 poin)
     * - Trigger notification job jika alert
     *
     * @param array $payload Job payload dengan id_periode
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

        // Calculate NRR (Nilai Rata-Rata) per unsur
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

        // Calculate NRR and NRR Tertimbang
        $totalNrrTertimbang = 0;
        $unsurResults = [];

        foreach ($unsurScores as $code => $data) {
            // NRR = Total nilai / Jumlah responden
            $nrr = $data['total'] / $data['count'];
            
            // NRR Tertimbang = NRR × Bobot (0.111)
            $bobot = 0.111; // Bobot standar setiap unsur
            $nrrTertimbang = $nrr * $bobot;

            $unsurResults[] = [
                'unsur_code' => $code,
                'nrr' => round($nrr, 4),
                'bobot' => $bobot,
                'nrr_tertimbang' => round($nrrTertimbang, 4),
                'response_count' => $data['count']
            ];

            $totalNrrTertimbang += $nrrTertimbang;
        }

        // IKM Total = Σ NRR_t × 25 (konversi ke skala 0-100)
        $ikmTotal = $totalNrrTertimbang * 25;

        // Determine quality category (Permenpan RB No. 14 Tahun 2017)
        $category = $this->getQualityCategory($ikmTotal);

        // Calculate delta vs previous period
        $deltaInfo = $this->calculateDelta($db, $idUnit, $idPeriode, $ikmTotal);

        // Check if alert needed (penurunan > threshold)
        $threshold = $payload['threshold'] ?? 5.0; // Default 5 poin
        $flagAlert = false;
        
        if ($deltaInfo['delta'] !== null && $deltaInfo['delta'] < -$threshold) {
            $flagAlert = true;
            // Trigger notification job
            $this->triggerNotification($idUnit, $idPeriode, $ikmTotal, $deltaInfo);
        }

        $result = [
            'status' => 'success',
            'id_unit' => $idUnit,
            'id_periode' => $idPeriode,
            'ikm_total' => round($ikmTotal, 2),
            'total_nrr_tertimbang' => round($totalNrrTertimbang, 4),
            'category' => $category,
            'unsur_details' => $unsurResults,
            'total_responses' => count(array_unique(array_column($answersQuery, 'id_responden'))),
            'delta' => $deltaInfo['delta'],
            'previous_ikm' => $deltaInfo['previous_ikm'],
            'flag_alert' => $flagAlert,
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
     * Calculate delta IKM vs periode sebelumnya
     */
    protected function calculateDelta($db, int $idUnit, int $idPeriode, float $currentIkm): array
    {
        // Get previous period IKM
        $previousPeriod = $db->table('tb_periode')
            ->select('id_periode')
            ->where('id_unit', $idUnit)
            ->where('id_periode <', $idPeriode)
            ->orderBy('id_periode', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        if (!$previousPeriod) {
            return ['delta' => null, 'previous_ikm' => null];
        }

        $previousIkm = $db->table('tb_rekap_ikm')
            ->select('nilai_ikm')
            ->where('id_unit', $idUnit)
            ->where('id_periode', $previousPeriod['id_periode'])
            ->get()
            ->getRowArray();

        if (!$previousIkm) {
            return ['delta' => null, 'previous_ikm' => null];
        }

        $previousScore = (float)$previousIkm['nilai_ikm'];
        $delta = $currentIkm - $previousScore;

        return [
            'delta' => round($delta, 2),
            'previous_ikm' => $previousScore
        ];
    }

    /**
     * Trigger notification job jika ada alert
     */
    protected function triggerNotification(int $idUnit, int $idPeriode, float $ikmTotal, array $deltaInfo): void
    {
        $notificationData = [
            'type' => 'ikm_alert',
            'priority' => 'high',
            'data' => [
                'id_unit' => $idUnit,
                'id_periode' => $idPeriode,
                'ikm_current' => $ikmTotal,
                'ikm_previous' => $deltaInfo['previous_ikm'],
                'delta' => $deltaInfo['delta'],
                'message' => "Penurunan IKM sebesar " . abs($deltaInfo['delta']) . " poin pada periode {$idPeriode}"
            ],
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Push to notification queue
        $this->redis->rpush(
            'notification',
            json_encode($notificationData, JSON_UNESCAPED_UNICODE)
        );

        log_message('info', "[KalkulasiIKMJob] Notification triggered for IKM alert: Unit {$idUnit}, Periode {$idPeriode}");
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
            'nilai_ikm' => $result['ikm_total'],
            'kategori' => $result['category']['code'],
            'predikat' => $result['category']['label'],
            'jumlah_responden' => $result['total_responses'],
            'delta_ikm' => $result['delta'],
            'flag_alert' => $result['flag_alert'] ? 1 : 0,
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
