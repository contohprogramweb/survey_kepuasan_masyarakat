<?php

namespace App\Models;

use CodeIgniter\Model;

class DashboardModel extends Model
{
    protected $table = 'tb_survei_responses';
    protected $allowedFields = ['id_unit', 'periode_id', 'tahun', 'unsur_id', 'nilai', 'responden_id'];
    protected $useTimestamps = false;

    /**
     * Get Tren IKM per Unit dalam periode tertentu
     * 
     * @param int|null $unitId
     * @param int|null $tahun
     * @return array
     */
    public function getTrenIkm($unitId = null, $tahun = null)
    {
        $builder = $this->db->table('tb_survei_responses r');
        $builder->select('p.nama_periode, p.tahun, p.urutan, u.nama_unit, AVG(r.nilai) as ikm_avg');
        $builder->join('tb_periode p', 'r.periode_id = p.id');
        $builder->join('tb_unit_layanan u', 'r.unit_id = u.id');
        $builder->groupBy('p.id, u.id');
        $builder->orderBy('p.tahun', 'ASC');
        $builder->orderBy('p.urutan', 'ASC');

        if ($unitId) {
            $builder->where('r.unit_id', $unitId);
        }
        if ($tahun) {
            $builder->where('p.tahun', $tahun);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Distribusi Jawaban per Unsur (Pie/Bar Chart)
     * 
     * @param int|null $unitId
     * @param int|null $periodeId
     * @return array
     */
    public function getDistribusiUnsur($unitId = null, $periodeId = null)
    {
        $builder = $this->db->table('tb_survei_responses r');
        $builder->select('un.nama_unsur, COUNT(r.id) as total_jawaban, AVG(r.nilai) as rata_rata');
        $builder->join('tb_unsur_pelayanan un', 'r.unsur_id = un.id');
        $builder->groupBy('un.id, un.nama_unsur');
        
        if ($unitId) {
            $builder->where('r.unit_id', $unitId);
        }
        if ($periodeId) {
            $builder->where('r.periode_id', $periodeId);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Rekapitulasi Periode: Total Responden, Nilai IKM, Mutu, Delta
     * 
     * @param int|null $unitId
     * @param int|null $tahun
     * @return array
     */
    public function getRekapitulasi($unitId = null, $tahun = null)
    {
        $sql = "SELECT 
                    p.id as periode_id,
                    p.nama_periode,
                    p.tahun,
                    p.urutan,
                    COUNT(DISTINCT r.responden_id) as total_responden,
                    AVG(r.nilai) as nilai_ikm,
                    CASE 
                        WHEN AVG(r.nilai) >= 85 THEN 'Sangat Baik'
                        WHEN AVG(r.nilai) >= 70 THEN 'Baik'
                        WHEN AVG(r.nilai) >= 55 THEN 'Kurang Baik'
                        ELSE 'Tidak Baik'
                    END as mutu_pelayanan
                FROM tb_survei_responses r
                JOIN tb_periode p ON r.periode_id = p.id
                WHERE 1=1";

        $params = [];
        if ($unitId) {
            $sql .= " AND r.unit_id = ?";
            $params[] = $unitId;
        }
        if ($tahun) {
            $sql .= " AND p.tahun = ?";
            $params[] = $tahun;
        }

        $sql .= " GROUP BY p.id, p.nama_periode, p.tahun, p.urutan ORDER BY p.tahun DESC, p.urutan DESC";

        $query = $this->db->query($sql, $params);
        $results = $query->getResultArray();

        // Hitung Delta (Perubahan dari periode sebelumnya)
        foreach ($results as $key => $row) {
            $prevIndex = $key + 1;
            if (isset($results[$prevIndex])) {
                $results[$key]['delta'] = round($row['nilai_ikm'] - $results[$prevIndex]['nilai_ikm'], 2);
            } else {
                $results[$key]['delta'] = 0;
            }
        }

        return $results;
    }

    /**
     * Cek Penurunan IKM (Alert)
     * Mengembalikan list unit yang mengalami penurunan signifikan dibanding periode lalu
     * 
     * @param int|null $tahun
     * @return array
     */
    public function getAlertPenurunan($tahun = null)
    {
        $tahun = $tahun ?? date('Y');
        
        $sql = "SELECT 
                    u.id as unit_id,
                    u.nama_unit, 
                    curr.ikm as ikm_sekarang, 
                    prev.ikm as ikm_lalu, 
                    (curr.ikm - prev.ikm) as selisih
                FROM tb_unit_layanan u
                JOIN (
                    SELECT unit_id, AVG(nilai) as ikm, periode_id 
                    FROM tb_survei_responses 
                    WHERE periode_id = (
                        SELECT id FROM tb_periode 
                        WHERE tahun = ? 
                        ORDER BY urutan DESC 
                        LIMIT 1
                    )
                    GROUP BY unit_id
                ) curr ON u.id = curr.unit_id
                LEFT JOIN (
                    SELECT unit_id, AVG(nilai) as ikm, periode_id 
                    FROM tb_survei_responses 
                    WHERE periode_id = (
                        SELECT id FROM tb_periode 
                        WHERE tahun = ? 
                        ORDER BY urutan DESC 
                        LIMIT 1 OFFSET 1
                    )
                    GROUP BY unit_id
                ) prev ON u.id = prev.unit_id
                WHERE (curr.ikm - prev.ikm) < -2.0";

        $query = $this->db->query($sql, [$tahun, $tahun]);
        return $query->getResultArray();
    }

    /**
     * Get semua unit layanan untuk filter
     * 
     * @return array
     */
    public function getAllUnits()
    {
        return $this->db->table('tb_unit_layanan')
            ->select('id, nama_unit')
            ->orderBy('nama_unit', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get semua periode untuk filter
     * 
     * @param int|null $tahun
     * @return array
     */
    public function getAllPeriodes($tahun = null)
    {
        $builder = $this->db->table('tb_periode')
            ->select('id, nama_periode, tahun, urutan')
            ->orderBy('tahun', 'DESC')
            ->orderBy('urutan', 'DESC');

        if ($tahun) {
            $builder->where('tahun', $tahun);
        }

        return $builder->get()->getResultArray();
    }
}
