<?php

namespace App\Models;

use CodeIgniter\Model;

class LaporanModel extends Model
{
    protected $table = 'laporan_jobs';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id',
        'jenis_laporan', // 'pdf' atau 'excel'
        'unit_id',
        'periode_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'status', // 'pending', 'processing', 'completed', 'failed'
        'file_path',
        'file_name',
        'error_message',
        'progress',
        'created_at',
        'updated_at',
        'completed_at'
    ];
    
    protected $useTimestamps = false;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    
    protected $validationRules = [
        'user_id' => 'required|integer',
        'jenis_laporan' => 'required|in_list[pdf,excel]',
        'status' => 'required|in_list[pending,processing,completed,failed]'
    ];
    
    protected $validationMessages = [
        'user_id' => [
            'required' => 'User ID harus diisi'
        ],
        'jenis_laporan' => [
            'required' => 'Jenis laporan harus dipilih',
            'in_list' => 'Jenis laporan tidak valid'
        ]
    ];
    
    /**
     * Get laporan jobs by user
     */
    public function getByUser(int $userId, int $limit = 20)
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }
    
    /**
     * Get job by ID and user
     */
    public function getByIdAndUser(int $jobId, int $userId)
    {
        return $this->where('id', $jobId)
                    ->where('user_id', $userId)
                    ->first();
    }
    
    /**
     * Update job status
     */
    public function updateStatus(int $jobId, string $status, ?string $errorMessage = null, ?string $filePath = null)
    {
        $data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($errorMessage !== null) {
            $data['error_message'] = $errorMessage;
        }
        
        if ($filePath !== null) {
            $data['file_path'] = $filePath;
        }
        
        if ($status === 'completed') {
            $data['completed_at'] = date('Y-m-d H:i:s');
            $data['progress'] = 100;
        }
        
        return $this->update($jobId, $data);
    }
    
    /**
     * Update job progress
     */
    public function updateProgress(int $jobId, int $progress)
    {
        return $this->update($jobId, [
            'progress' => $progress,
            'status' => 'processing',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Get pending jobs
     */
    public function getPendingJobs(string $jenisLaporan = null)
    {
        $query = $this->where('status', 'pending');
        
        if ($jenisLaporan !== null) {
            $query->where('jenis_laporan', $jenisLaporan);
        }
        
        return $query->orderBy('created_at', 'ASC')->findAll();
    }
    
    /**
     * Clean old completed jobs (older than 7 days)
     */
    public function cleanOldJobs(int $days = 7)
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        return $this->where('status', 'completed')
                    ->where('completed_at <', $cutoffDate)
                    ->delete();
    }
    
    /**
     * Get statistics
     */
    public function getStatistics(int $userId = null)
    {
        $query = $this->select('status, COUNT(*) as count')
                      ->groupBy('status');
        
        if ($userId !== null) {
            $query->where('user_id', $userId);
        }
        
        $results = $query->get()->getResultArray();
        
        $stats = [
            'pending' => 0,
            'processing' => 0,
            'completed' => 0,
            'failed' => 0,
            'total' => 0
        ];
        
        foreach ($results as $row) {
            $stats[$row['status']] = (int)$row['count'];
            $stats['total'] += (int)$row['count'];
        }
        
        return $stats;
    }
}
