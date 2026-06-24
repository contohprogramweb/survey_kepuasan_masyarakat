<?php

namespace App\Models;

use CodeIgniter\Model;

class SurveiModel extends Model
{
    protected $table = 'tb_survei_jawaban';
    protected $primaryKey = 'id_jawaban';
    protected $allowedFields = [
        'id_responden',
        'id_kuesioner',
        'id_periode',
        'id_unit',
        'nilai',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = null;
    protected $deletedField = null;

    /**
     * Get 9 unsur wajib kuesioner aktif
     */
    public function getUnsurWajib(): array
    {
        $db = \Config\Database::connect();
        $query = $db->table('tb_kuesioner')
            ->select('id_kuesioner, unsur_code, nama_unsur, deskripsi')
            ->where('is_active', 1)
            ->where('deleted_at', null)
            ->orderBy('urutan', 'ASC')
            ->get();
        
        return $query->getResultArray();
    }

    /**
     * Save survey answers
     */
    public function saveAnswers(array $data): bool
    {
        $db = \Config\Database::connect();
        $builder = $db->table($this->table);
        
        return $builder->insertBatch($data);
    }

    /**
     * Check duplicate submission by fingerprint
     */
    public function checkDuplicate(string $fingerprint, int $idUnit, int $idPeriode): bool
    {
        $db = \Config\Database::connect();
        $query = $db->table($this->table . ' sj')
            ->join('tb_responden r', 'r.id_responden = sj.id_responden')
            ->where('r.device_fingerprint', $fingerprint)
            ->where('sj.id_unit', $idUnit)
            ->where('sj.id_periode', $idPeriode);
        
        $result = $query->countAllResults();
        return $result > 0;
    }

    /**
     * Get survey statistics for a unit and period
     */
    public function getStatistics(int $idUnit, int $idPeriode): array
    {
        $db = \Config\Database::connect();
        
        // Total responses
        $totalQuery = $db->table($this->table . ' sj')
            ->selectCount('sj.id_jawaban', 'total')
            ->join('tb_responden r', 'r.id_responden = sj.id_responden')
            ->where('sj.id_unit', $idUnit)
            ->where('sj.id_periode', $idPeriode)
            ->get()
            ->getRowArray();
        
        // Average per unsur
        $avgQuery = $db->table($this->table . ' sj')
            ->select('k.unsur_code, k.nama_unsur, AVG(sj.nilai) as avg_nilai, COUNT(sj.id_jawaban) as count')
            ->join('tb_kuesioner k', 'k.id_kuesioner = sj.id_kuesioner')
            ->join('tb_responden r', 'r.id_responden = sj.id_responden')
            ->where('sj.id_unit', $idUnit)
            ->where('sj.id_periode', $idPeriode)
            ->groupBy('k.id_kuesioner')
            ->orderBy('k.urutan', 'ASC')
            ->get()
            ->getResultArray();
        
        return [
            'total_responses' => $totalQuery['total'] ?? 0,
            'average_per_unsur' => $avgQuery
        ];
    }

    // =========================================================================
    // Survey Element (Unsur) Management Methods
    // =========================================================================

    /**
     * Get all survey elements
     */
    public function getAllElements(): array
    {
        $db = \Config\Database::connect();
        $query = $db->table('tb_kuesioner')
            ->select('*')
            ->orderBy('urutan', 'ASC')
            ->get();
        
        return $query->getResultArray();
    }

    /**
     * Get active survey elements
     */
    public function getActiveElements(): array
    {
        $db = \Config\Database::connect();
        $query = $db->table('tb_kuesioner')
            ->select('id_kuesioner, unsur_code, nama_unsur, deskripsi, bobot')
            ->where('is_active', 1)
            ->orderBy('urutan', 'ASC')
            ->get();
        
        return $query->getResultArray();
    }

    /**
     * Get element by ID
     */
    public function getElementById(int $id): ?array
    {
        $db = \Config\Database::connect();
        $query = $db->table('tb_kuesioner')
            ->where('id_kuesioner', $id)
            ->get();
        
        return $query->getRowArray();
    }

    /**
     * Insert new element
     */
    public function insertElement(array $data): int
    {
        $db = \Config\Database::connect();
        $builder = $db->table('tb_kuesioner');
        $builder->insert($data);
        return $db->insertID();
    }

    /**
     * Update element
     */
    public function updateElement(int $id, array $data): bool
    {
        $db = \Config\Database::connect();
        $builder = $db->table('tb_kuesioner');
        return $builder->update($data, ['id_kuesioner' => $id]);
    }

    /**
     * Delete element
     */
    public function deleteElement(int $id): bool
    {
        $db = \Config\Database::connect();
        $builder = $db->table('tb_kuesioner');
        return $builder->delete(['id_kuesioner' => $id]);
    }

    /**
     * Check if element has questions
     */
    public function hasQuestions(int $elementId): bool
    {
        $db = \Config\Database::connect();
        $query = $db->table('tb_pertanyaan')
            ->where('id_kuesioner', $elementId);
        
        $result = $query->countAllResults();
        return $result > 0;
    }

    // =========================================================================
    // Survey Question (Pertanyaan) Management Methods
    // =========================================================================

    /**
     * Get all survey questions with element info
     */
    public function getAllQuestions(): array
    {
        $db = \Config\Database::connect();
        $query = $db->table('tb_pertanyaan p')
            ->select('p.*, k.unsur_code, k.nama_unsur as nama_unsur')
            ->join('tb_kuesioner k', 'k.id_kuesioner = p.id_kuesioner', 'left')
            ->orderBy('p.urutan', 'ASC')
            ->get();
        
        return $query->getResultArray();
    }

    /**
     * Get question by ID
     */
    public function getQuestionById(int $id): ?array
    {
        $db = \Config\Database::connect();
        $query = $db->table('tb_pertanyaan')
            ->where('id_pertanyaan', $id)
            ->get();
        
        return $query->getRowArray();
    }

    /**
     * Get questions by element
     */
    public function getQuestionsByElement(int $elementId): array
    {
        $db = \Config\Database::connect();
        $query = $db->table('tb_pertanyaan')
            ->where('id_kuesioner', $elementId)
            ->orderBy('urutan', 'ASC')
            ->get();
        
        return $query->getResultArray();
    }

    /**
     * Insert new question
     */
    public function insertQuestion(array $data): int
    {
        $db = \Config\Database::connect();
        $builder = $db->table('tb_pertanyaan');
        $builder->insert($data);
        return $db->insertID();
    }

    /**
     * Update question
     */
    public function updateQuestion(int $id, array $data): bool
    {
        $db = \Config\Database::connect();
        $builder = $db->table('tb_pertanyaan');
        return $builder->update($data, ['id_pertanyaan' => $id]);
    }

    /**
     * Delete question
     */
    public function deleteQuestion(int $id): bool
    {
        $db = \Config\Database::connect();
        $builder = $db->table('tb_pertanyaan');
        return $builder->delete(['id_pertanyaan' => $id]);
    }

    /**
     * Check if question has responses
     */
    public function hasResponses(int $questionId): bool
    {
        $db = \Config\Database::connect();
        $query = $db->table('tb_survei_jawaban')
            ->where('id_kuesioner', $questionId);
        
        $result = $query->countAllResults();
        return $result > 0;
    }
}
