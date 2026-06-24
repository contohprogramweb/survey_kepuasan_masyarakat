<?php

namespace App\Services;

use Config\Database;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use Exception;

/**
 * LaporanService
 * 
 * Service untuk generate laporan IKM (PDF dan Excel)
 * Berdasarkan SRS F-08 dan F-09
 */
class LaporanService
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * Get data rekapitulasi IKM per periode
     * 
     * @param int|null $idUnit Filter by unit
     * @param string|null $startDate Start date filter
     * @param string|null $endDate End date filter
     * @return array
     */
    public function getRekapitulasiPeriode(?int $idUnit = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $builder = $this->db->table('tb_rekap_ikm r')
            ->select('
                r.id_periode,
                p.nama_periode,
                p.tahun,
                u.id_unit,
                u.nama_unit,
                r.nilai_ikm,
                r.kategori,
                r.predikat,
                r.jumlah_responden,
                r.delta_ikm,
                r.created_at as calculated_at
            ')
            ->join('tb_periode p', 'p.id_periode = r.id_periode')
            ->join('tb_unit_layanan u', 'u.id_unit = r.id_unit');

        if ($idUnit !== null) {
            $builder->where('r.id_unit', $idUnit);
        }

        if ($startDate !== null) {
            $builder->where('p.tanggal_mulai >=', $startDate);
        }

        if ($endDate !== null) {
            $builder->where('p.tanggal_selesai <=', $endDate);
        }

        $result = $builder->orderBy('p.tahun', 'DESC')
            ->orderBy('p.urutan', 'DESC')
            ->get()
            ->getResultArray();

        // Get detail unsur untuk setiap rekap
        foreach ($result as &$row) {
            $detailUnsur = json_decode($row['detail_unsur'] ?? '[]', true);
            $row['unsur_details'] = is_array($detailUnsur) ? $detailUnsur : [];
        }

        return $result;
    }

    /**
     * Get data mentah responden untuk ekspor
     * 
     * @param int|null $idUnit Filter by unit
     * @param int|null $idPeriode Filter by periode
     * @return array
     */
    public function getDataResponden(?int $idUnit = null, ?int $idPeriode = null): array
    {
        $builder = $this->db->table('tb_responden r')
            ->select('
                r.id_responden,
                r.nik,
                r.nama,
                r.jenis_kelamin,
                r.usia,
                r.pendidikan,
                r.pekerjaan,
                r.email,
                r.telepon,
                r.consent_given,
                r.ip_address,
                r.created_at,
                u.nama_unit,
                p.nama_periode
            ')
            ->join('tb_unit_layanan u', 'u.id_unit = r.id_unit')
            ->join('tb_periode p', 'p.id_periode = r.id_periode');

        if ($idUnit !== null) {
            $builder->where('r.id_unit', $idUnit);
        }

        if ($idPeriode !== null) {
            $builder->where('r.id_periode', $idPeriode);
        }

        return $builder->orderBy('r.created_at', 'DESC')->get()->getResultArray();
    }

    /**
     * Get data jawaban responden untuk ekspor
     * 
     * @param int|null $idUnit Filter by unit
     * @param int|null $idPeriode Filter by periode
     * @return array
     */
    public function getDataJawaban(?int $idUnit = null, ?int $idPeriode = null): array
    {
        $builder = $this->db->table('tb_survei_jawaban sj')
            ->select('
                sj.id_jawaban,
                r.nik,
                r.nama,
                k.unsur_code,
                k.nama_unsur,
                sj.nilai,
                u.nama_unit,
                p.nama_periode,
                sj.created_at
            ')
            ->join('tb_responden r', 'r.id_responden = sj.id_responden')
            ->join('tb_kuesioner k', 'k.id_kuesioner = sj.id_kuesioner')
            ->join('tb_unit_layanan u', 'u.id_unit = sj.id_unit')
            ->join('tb_periode p', 'p.id_periode = sj.id_periode');

        if ($idUnit !== null) {
            $builder->where('sj.id_unit', $idUnit);
        }

        if ($idPeriode !== null) {
            $builder->where('sj.id_periode', $idPeriode);
        }

        return $builder->orderBy('sj.created_at', 'DESC')->get()->getResultArray();
    }

    /**
     * Get statistik untuk summary laporan
     * 
     * @param int|null $idUnit
     * @param int|null $idPeriode
     * @return array
     */
    public function getStatistikSummary(?int $idUnit = null, ?int $idPeriode = null): array
    {
        $builder = $this->db->table('tb_rekap_ikm r')
            ->select('
                COUNT(*) as total_periode,
                AVG(r.nilai_ikm) as rata_ikm,
                MIN(r.nilai_ikm) as min_ikm,
                MAX(r.nilai_ikm) as max_ikm,
                SUM(r.jumlah_responden) as total_responden
            ');

        if ($idUnit !== null) {
            $builder->where('r.id_unit', $idUnit);
        }

        if ($idPeriode !== null) {
            $builder->where('r.id_periode', $idPeriode);
        }

        $summary = $builder->get()->getRowArray();

        // Get distribution by category
        $categoryBuilder = $this->db->table('tb_rekap_ikm r')
            ->select('r.kategori, r.predikat, COUNT(*) as jumlah')
            ->groupBy('r.kategori, r.predikat');

        if ($idUnit !== null) {
            $categoryBuilder->where('r.id_unit', $idUnit);
        }

        $categoryDist = $categoryBuilder->get()->getResultArray();

        return [
            'summary' => $summary ?? [],
            'category_distribution' => $categoryDist
        ];
    }

    /**
     * Get metadata instansi untuk header laporan
     * 
     * @return array
     */
    public function getInstansiMetadata(): array
    {
        // Bisa disesuaikan dengan data instansi yang sebenarnya
        return [
            'nama_instansi' => env('IKM_INSTANSI_NAMA', 'Pemerintah Daerah'),
            'alamat' => env('IKM_INSTANSI_ALAMAT', 'Jl. Pemerintahan No. 1'),
            'telepon' => env('IKM_INSTANSI_TELEPON', '(021) 1234567'),
            'email' => env('IKM_INSTANSI_EMAIL', 'info@pemda.go.id'),
            'website' => env('IKM_INSTANSI_WEBSITE', 'www.pemda.go.id'),
            'logo_path' => env('IKM_INSTANSI_LOGO', '/assets/img/logo.png'),
        ];
    }

    /**
     * Generate Excel file dengan multiple sheets
     * 
     * @param array $filters Filter parameters
     * @return string Path ke file Excel
     * @throws Exception
     */
    public function generateExcel(array $filters = []): string
    {
        $spreadsheet = new Spreadsheet();
        
        // Hapus sheet default
        $spreadsheet->removeSheetByIndex(0);

        // Sheet 1: Rekapitulasi
        $rekapSheet = $this->createRekapitulasiSheet($filters);
        $spreadsheet->addSheet($rekapSheet);

        // Sheet 2: Detail Responden
        $detailSheet = $this->createDetailRespondenSheet($filters);
        $spreadsheet->addSheet($detailSheet);

        // Sheet 3: Data Jawaban
        $jawabanSheet = $this->createDataJawabanSheet($filters);
        $spreadsheet->addSheet($jawabanSheet);

        // Sheet 4: Metadata
        $metadataSheet = $this->createMetadataSheet($filters);
        $spreadsheet->addSheet($metadataSheet);

        // Set active sheet
        $spreadsheet->setActiveSheetIndex(0);

        // Generate filename
        $filename = 'Laporan_IKM_' . date('Y-m-d_His') . '.xlsx';
        $filepath = WRITEPATH . 'exports/' . $filename;

        // Ensure directory exists
        if (!is_dir(WRITEPATH . 'exports')) {
            mkdir(WRITEPATH . 'exports', 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($filepath);

        return $filepath;
    }

    /**
     * Create sheet untuk rekapitulasi
     */
    protected function createRekapitulasiSheet(array $filters)
    {
        $sheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet();
        $sheet->setTitle('Rekapitulasi');

        $data = $this->getRekapitulasiPeriode(
            $filters['id_unit'] ?? null,
            $filters['start_date'] ?? null,
            $filters['end_date'] ?? null
        );

        $row = 1;
        $col = 1;

        // Header
        $sheet->setCellValueExplicit('A' . $row, 'LAPORAN REKAPITULASI IKM', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->mergeCells('A' . $row . ':H' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;

        $row++; // Empty row

        // Column headers
        $headers = [
            'No',
            'Unit Layanan',
            'Periode',
            'Tahun',
            'Nilai IKM',
            'Kategori',
            'Predikat',
            'Jumlah Responden'
        ];

        $col = 1;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($col, $row, $header);
            $col++;
        }

        // Style header
        $headerRange = 'A' . $row . ':H' . $row;
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD3D3D3');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $row++;

        // Data rows
        $no = 1;
        foreach ($data as $item) {
            $sheet->setCellValueByColumnAndRow(1, $row, $no++);
            $sheet->setCellValueByColumnAndRow(2, $row, $item['nama_unit']);
            $sheet->setCellValueByColumnAndRow(3, $row, $item['nama_periode']);
            $sheet->setCellValueByColumnAndRow(4, $row, $item['tahun']);
            $sheet->setCellValueByColumnAndRow(5, $row, number_format($item['nilai_ikm'], 2));
            $sheet->setCellValueByColumnAndRow(6, $row, $item['kategori']);
            $sheet->setCellValueByColumnAndRow(7, $row, $item['predikat']);
            $sheet->setCellValueByColumnAndRow(8, $row, $item['jumlah_responden']);

            // Apply borders
            $range = 'A' . $row . ':H' . $row;
            $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            // Color code based on category
            $color = $this->getCategoryColor($item['kategori']);
            $sheet->getStyle('E' . $row)->getFill()->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB($color);

            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $sheet;
    }

    /**
     * Create sheet untuk detail responden
     */
    protected function createDetailRespondenSheet(array $filters)
    {
        $sheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet();
        $sheet->setTitle('Detail Responden');

        $data = $this->getDataResponden(
            $filters['id_unit'] ?? null,
            $filters['id_periode'] ?? null
        );

        $row = 1;

        // Header
        $sheet->setCellValueExplicit('A' . $row, 'DATA MENTAH RESPONDEN', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->mergeCells('A' . $row . ':K' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;

        $row++;

        // Column headers
        $headers = [
            'No', 'NIK', 'Nama', 'Jenis Kelamin', 'Usia', 'Pendidikan',
            'Pekerjaan', 'Email', 'Telepon', 'Unit', 'Periode', 'Tanggal'
        ];

        $col = 1;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($col, $row, $header);
            $col++;
        }

        // Style header
        $headerRange = 'A' . $row . ':L' . $row;
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD3D3D3');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $row++;

        // Data rows
        $no = 1;
        foreach ($data as $item) {
            $sheet->setCellValueByColumnAndRow(1, $row, $no++);
            $sheet->setCellValueByColumnAndRow(2, $row, $item['nik'] ?? '-');
            $sheet->setCellValueByColumnAndRow(3, $row, $item['nama'] ?? '-');
            $sheet->setCellValueByColumnAndRow(4, $row, $item['jenis_kelamin'] ?? '-');
            $sheet->setCellValueByColumnAndRow(5, $row, $item['usia'] ?? '-');
            $sheet->setCellValueByColumnAndRow(6, $row, $item['pendidikan'] ?? '-');
            $sheet->setCellValueByColumnAndRow(7, $row, $item['pekerjaan'] ?? '-');
            $sheet->setCellValueByColumnAndRow(8, $row, $item['email'] ?? '-');
            $sheet->setCellValueByColumnAndRow(9, $row, $item['telepon'] ?? '-');
            $sheet->setCellValueByColumnAndRow(10, $row, $item['nama_unit']);
            $sheet->setCellValueByColumnAndRow(11, $row, $item['nama_periode']);
            $sheet->setCellValueByColumnAndRow(12, $row, $item['created_at']);

            $range = 'A' . $row . ':L' . $row;
            $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $sheet;
    }

    /**
     * Create sheet untuk data jawaban
     */
    protected function createDataJawabanSheet(array $filters)
    {
        $sheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet();
        $sheet->setTitle('Data Jawaban');

        $data = $this->getDataJawaban(
            $filters['id_unit'] ?? null,
            $filters['id_periode'] ?? null
        );

        $row = 1;

        // Header
        $sheet->setCellValueExplicit('A' . $row, 'DATA JAWABAN RESPONDEN PER UNSUR', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->mergeCells('A' . $row . ':G' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;

        $row++;

        // Column headers
        $headers = [
            'No', 'NIK', 'Nama', 'Unsur', 'Nama Unsur', 'Nilai', 'Tanggal'
        ];

        $col = 1;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($col, $row, $header);
            $col++;
        }

        // Style header
        $headerRange = 'A' . $row . ':G' . $row;
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD3D3D3');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $row++;

        // Data rows
        $no = 1;
        foreach ($data as $item) {
            $sheet->setCellValueByColumnAndRow(1, $row, $no++);
            $sheet->setCellValueByColumnAndRow(2, $row, $item['nik'] ?? '-');
            $sheet->setCellValueByColumnAndRow(3, $row, $item['nama'] ?? '-');
            $sheet->setCellValueByColumnAndRow(4, $row, $item['unsur_code']);
            $sheet->setCellValueByColumnAndRow(5, $row, $item['nama_unsur']);
            $sheet->setCellValueByColumnAndRow(6, $row, $item['nilai']);
            $sheet->setCellValueByColumnAndRow(7, $row, $item['created_at']);

            $range = 'A' . $row . ':G' . $row;
            $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $sheet;
    }

    /**
     * Create sheet untuk metadata
     */
    protected function createMetadataSheet(array $filters)
    {
        $sheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet();
        $sheet->setTitle('Metadata');

        $metadata = $this->getInstansiMetadata();
        $stats = $this->getStatistikSummary(
            $filters['id_unit'] ?? null,
            $filters['id_periode'] ?? null
        );

        $row = 1;

        // Header
        $sheet->setCellValueExplicit('A' . $row, 'METADATA LAPORAN', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->mergeCells('A' . $row . ':B' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;

        $row++;

        // Instansi info
        $sheet->setCellValue('A' . $row, 'Nama Instansi:');
        $sheet->setCellValue('B' . $row, $metadata['nama_instansi']);
        $row++;

        $sheet->setCellValue('A' . $row, 'Alamat:');
        $sheet->setCellValue('B' . $row, $metadata['alamat']);
        $row++;

        $sheet->setCellValue('A' . $row, 'Telepon:');
        $sheet->setCellValue('B' . $row, $metadata['telepon']);
        $row++;

        $sheet->setCellValue('A' . $row, 'Email:');
        $sheet->setCellValue('B' . $row, $metadata['email']);
        $row++;

        $sheet->setCellValue('A' . $row, 'Website:');
        $sheet->setCellValue('B' . $row, $metadata['website']);
        $row++;

        $row++;

        // Statistik
        $sheet->setCellValue('A' . $row, 'STATISTIK');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        if (!empty($stats['summary'])) {
            $sheet->setCellValue('A' . $row, 'Total Periode:');
            $sheet->setCellValue('B' . $row, $stats['summary']['total_periode'] ?? 0);
            $row++;

            $sheet->setCellValue('A' . $row, 'Rata-rata IKM:');
            $sheet->setCellValue('B' . $row, number_format($stats['summary']['rata_ikm'] ?? 0, 2));
            $row++;

            $sheet->setCellValue('A' . $row, 'Nilai IKM Tertinggi:');
            $sheet->setCellValue('B' . $row, number_format($stats['summary']['max_ikm'] ?? 0, 2));
            $row++;

            $sheet->setCellValue('A' . $row, 'Nilai IKM Terendah:');
            $sheet->setCellValue('B' . $row, number_format($stats['summary']['min_ikm'] ?? 0, 2));
            $row++;

            $sheet->setCellValue('A' . $row, 'Total Responden:');
            $sheet->setCellValue('B' . $row, $stats['summary']['total_responden'] ?? 0);
            $row++;
        }

        $row++;

        // Distribusi kategori
        $sheet->setCellValue('A' . $row, 'DISTRIBUSI KATEGORI');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        if (!empty($stats['category_distribution'])) {
            foreach ($stats['category_distribution'] as $cat) {
                $sheet->setCellValue('A' . $row, "Kategori {$cat['kategori']} ({$cat['predikat']}):");
                $sheet->setCellValue('B' . $row, $cat['jumlah']);
                $row++;
            }
        }

        $row++;

        // Export info
        $sheet->setCellValue('A' . $row, 'INFORMASI EKSPOR');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        $sheet->setCellValue('A' . $row, 'Tanggal Ekspor:');
        $sheet->setCellValue('B' . $row, date('Y-m-d H:i:s'));
        $row++;

        $sheet->setCellValue('A' . $row, 'Filter Digunakan:');
        $sheet->setCellValue('B' . $row, json_encode($filters, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $row++;

        // Auto-size columns
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);

        return $sheet;
    }

    /**
     * Get color for category
     */
    protected function getCategoryColor(string $kategori): string
    {
        $colors = [
            'A' => 'FF28A745', // Green
            'B' => 'FF17A2B8', // Blue
            'C' => 'FFFFC107', // Yellow
            'D' => 'FFDC3545', // Red
        ];

        return $colors[$kategori] ?? 'FFFFFFFF';
    }

    /**
     * Generate HTML untuk PDF
     * 
     * @param array $filters
     * @return string
     */
    public function generatePdfHtml(array $filters = []): string
    {
        $metadata = $this->getInstansiMetadata();
        $rekapitulasi = $this->getRekapitulasiPeriode(
            $filters['id_unit'] ?? null,
            $filters['start_date'] ?? null,
            $filters['end_date'] ?? null
        );
        $stats = $this->getStatistikSummary(
            $filters['id_unit'] ?? null,
            $filters['id_periode'] ?? null
        );

        // Load view template
        $viewPath = APPPATH . 'Views/laporan/ikm_report.php';
        
        if (!file_exists($viewPath)) {
            throw new Exception("PDF template not found: {$viewPath}");
        }

        ob_start();
        include $viewPath;
        return ob_get_clean();
    }
}
