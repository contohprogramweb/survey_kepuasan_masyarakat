<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SurveiModel;

/**
 * KuesionerController
 * 
 * Controller untuk manajemen kuesioner/unsur IKM (UC-05, F-04)
 * Mengelola 9 unsur wajib IKM sesuai PermenPANRB 14/2017
 */
class KuesionerController extends BaseController
{
    protected SurveiModel $kuesionerModel;
    protected $validationRules = [
        'nama_unsur' => 'required|min_length[3]|max_length[100]',
        'kode_unsur' => 'required|alpha_dash|max_length[20]|is_unique[tb_kuesioner.kode_unsur,id_kuesioner,{id_kuesioner}]',
        'deskripsi' => 'permit_empty|max_length[500]',
        'bobot' => 'permit_empty|decimal',
        'urutan' => 'required|integer|greater_than[0]',
        'status' => 'permit_empty|in_list[aktif,nonaktif]',
    ];

    public function __construct()
    {
        $this->kuesionerModel = new SurveiModel();
    }

    /**
     * Display list of questionnaire elements
     */
    public function index(): string
    {
        $data = [
            'title' => 'Manajemen Kuesioner IKM',
            'elements' => $this->kuesionerModel->getAllElements(),
        ];

        return view('admin/kuesioner/index', $data);
    }

    /**
     * Get data for DataTables (server-side processing)
     */
    public function data()
    {
        $draw = $this->request->getVar('draw') ?? 1;
        $start = $this->request->getVar('start') ?? 0;
        $length = $this->request->getVar('length') ?? 10;
        $search = $this->request->getVar('search')['value'] ?? '';
        $orderColumn = $this->request->getVar('order')[0]['column'] ?? 0;
        $orderDir = $this->request->getVar('order')[0]['dir'] ?? 'asc';

        $columns = ['kode_unsur', 'nama_unsur', 'deskripsi', 'bobot', 'urutan', 'status'];
        $orderBy = $columns[$orderColumn] ?? 'urutan';

        // Build query
        $builder = $this->kuesionerModel->db->table('tb_kuesioner');
        
        // Search
        if (!empty($search)) {
            $builder->groupStart()
                ->like('nama_unsur', $search)
                ->orLike('kode_unsur', $search)
                ->orLike('deskripsi', $search)
                ->groupEnd();
        }

        // Count total before pagination
        $totalRecords = $builder->countAllResults(false);

        // Order and limit
        $builder->orderBy($orderBy, $orderDir);
        if ($length > 0) {
            $builder->limit($length, $start);
        }

        $results = $builder->get()->getResultArray();

        // Format data for DataTables
        foreach ($results as &$row) {
            $row['actions'] = view('admin/kuesioner/_actions', ['element' => $row]);
            $row['status_badge'] = $row['status'] === 'aktif' 
                ? '<span class="badge bg-success">Aktif</span>' 
                : '<span class="badge bg-secondary">Nonaktif</span>';
        }

        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $results,
        ]);
    }

    /**
     * Show questionnaire element details
     */
    public function show($id)
    {
        $element = $this->kuesionerModel->getElementById($id);
        
        if (!$element) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unsur tidak ditemukan.'
            ])->setStatusCode(404);
        }

        // Get related questions
        $questions = $this->kuesionerModel->getQuestionsByElement($id);

        return $this->response->setJSON([
            'success' => true,
            'data' => array_merge($element, ['questions' => $questions]),
        ]);
    }

    /**
     * Show form to edit element
     */
    public function edit($id): string
    {
        $element = $this->kuesionerModel->getElementById($id);
        
        if (!$element) {
            return redirect()->to('admin/kuesioner')->with('error', 'Unsur tidak ditemukan.');
        }

        $data = [
            'title' => 'Edit Unsur Kuesioner',
            'element' => $element,
            'validation' => \Config\Services::validation(),
        ];

        return view('admin/kuesioner/edit', $data);
    }

    /**
     * Update questionnaire element
     */
    public function update($id)
    {
        $element = $this->kuesionerModel->getElementById($id);
        
        if (!$element) {
            return redirect()->to('admin/kuesioner')->with('error', 'Unsur tidak ditemukan.');
        }

        // Validate input
        if (!$this->validate($this->validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Update element
        $this->kuesionerModel->updateElement($id, [
            'nama_unsur' => $this->request->getPost('nama_unsur'),
            'kode_unsur' => $this->request->getPost('kode_unsur'),
            'deskripsi' => $this->request->getPost('deskripsi'),
            'bobot' => $this->request->getPost('bobot') ?? 0.111,
            'urutan' => $this->request->getPost('urutan'),
            'status' => $this->request->getPost('status') ?? 'aktif',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Log audit trail
        log_message('info', 'Unsur kuesioner diperbarui: ' . $this->request->getPost('nama_unsur'));

        return redirect()->to('admin/kuesioner')->with('success', 'Unsur berhasil diperbarui.');
    }

    /**
     * Toggle element status
     */
    public function toggleStatus($id)
    {
        $element = $this->kuesionerModel->getElementById($id);
        
        if (!$element) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unsur tidak ditemukan.'
            ])->setStatusCode(404);
        }

        // Check if element has questions with responses
        if ($this->kuesionerModel->hasQuestions($id)) {
            $questions = $this->kuesionerModel->getQuestionsByElement($id);
            foreach ($questions as $question) {
                if ($this->kuesionerModel->hasResponses($question['id_pertanyaan'])) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Tidak dapat menonaktifkan unsur yang sudah memiliki respons.'
                    ]);
                }
            }
        }

        $newStatus = $element['status'] === 'aktif' ? 'nonaktif' : 'aktif';

        $this->kuesionerModel->updateElement($id, [
            'status' => $newStatus,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        log_message('info', "Status unsur {$element['nama_unsur']} diubah menjadi {$newStatus}");

        return $this->response->setJSON([
            'success' => true,
            'message' => "Status unsur berhasil diubah menjadi {$newStatus}.",
            'new_status' => $newStatus,
        ]);
    }

    /**
     * Delete questionnaire element
     */
    public function destroy($id)
    {
        $element = $this->kuesionerModel->getElementById($id);
        
        if (!$element) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unsur tidak ditemukan.'
            ])->setStatusCode(404);
        }

        // Check if element has questions
        if ($this->kuesionerModel->hasQuestions($id)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tidak dapat menghapus unsur yang masih memiliki pertanyaan.'
            ]);
        }

        // Delete element
        $this->kuesionerModel->deleteElement($id);

        log_message('info', 'Unsur kuesioner dihapus: ' . $element['nama_unsur']);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Unsur berhasil dihapus.',
        ]);
    }

    /**
     * Preview questionnaire (F-19, UC-19)
     */
    public function preview()
    {
        $data = [
            'title' => 'Preview Kuesioner',
            'elements' => $this->kuesionerModel->getActiveElements(),
            'preview_mode' => true,
        ];

        return view('admin/kuesioner/preview', $data);
    }

    /**
     * Reorder questionnaire elements
     */
    public function reorder()
    {
        $order = $this->request->getPost('order');
        
        if (!is_array($order)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data urutan tidak valid.'
            ]);
        }

        foreach ($order as $index => $id) {
            $this->kuesionerModel->updateElement($id, [
                'urutan' => $index + 1,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        log_message('info', 'Urutan unsur kuesioner diperbarui');

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Urutan berhasil diperbarui.',
        ]);
    }
}
