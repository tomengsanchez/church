<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\MemberStatusModel;

class SettingsController extends Controller
{
    private MemberStatusModel $memberStatusModel;

    public function __construct()
    {
        $this->memberStatusModel = new MemberStatusModel();
    }

    public function index(): void
    {
        $this->requireRole([ROLE_SUPER_ADMIN]);

        $statuses = $this->memberStatusModel->getAll();
        $this->view('settings/index', [
            'statuses' => $statuses
        ]);
    }

    public function createStatus(): void
    {
        $this->requireRole([ROLE_SUPER_ADMIN]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'slug' => strtolower(trim($_POST['slug'] ?? '')),
                'name' => trim($_POST['name'] ?? ''),
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'is_default' => isset($_POST['is_default']) ? 1 : 0,
                'sort_order' => (int)($_POST['sort_order'] ?? 0),
            ];

            if ($data['slug'] === '' || $data['name'] === '') {
                flash('Slug and Name are required.', 'error');
                $this->redirect('/settings');
            }

            if ($this->memberStatusModel->slugExists($data['slug'])) {
                flash('Slug already exists. Choose another.', 'error');
                $this->redirect('/settings');
            }

            $id = $this->memberStatusModel->createStatus($data);
            if ($id) {
                if ($data['is_default']) {
                    $this->memberStatusModel->setDefault($id);
                }
                flash('Member status created.', 'success');
            } else {
                flash('Failed to create member status.', 'error');
            }
            $this->redirect('/settings');
        }
    }

    public function editStatus(string $id): void
    {
        $this->requireRole([ROLE_SUPER_ADMIN]);
        $statusId = (int)$id;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'slug' => strtolower(trim($_POST['slug'] ?? '')),
                'name' => trim($_POST['name'] ?? ''),
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'is_default' => isset($_POST['is_default']) ? 1 : 0,
                'sort_order' => (int)($_POST['sort_order'] ?? 0),
            ];

            if ($data['slug'] === '' || $data['name'] === '') {
                flash('Slug and Name are required.', 'error');
                $this->redirect('/settings');
            }

            if ($this->memberStatusModel->slugExists($data['slug'], $statusId)) {
                flash('Slug already exists. Choose another.', 'error');
                $this->redirect('/settings');
            }

            if ($this->memberStatusModel->updateStatus($statusId, $data)) {
                if ($data['is_default']) {
                    $this->memberStatusModel->setDefault($statusId);
                }
                flash('Member status updated.', 'success');
            } else {
                flash('Failed to update member status.', 'error');
            }
        }

        $this->redirect('/settings');
    }

    public function deleteStatus(string $id): void
    {
        $this->requireRole([ROLE_SUPER_ADMIN]);
        $statusId = (int)$id;

        if ($this->memberStatusModel->deleteStatus($statusId)) {
            flash('Member status deleted.', 'success');
        } else {
            flash('Failed to delete member status.', 'error');
        }

        $this->redirect('/settings');
    }

    public function updateSortOrder(): void
    {
        $this->requireRole([ROLE_SUPER_ADMIN]);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['statuses']) || !is_array($input['statuses'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid data']);
            return;
        }

        $statuses = $input['statuses'];
        
        // Log the received data
        error_log("Received sort order update request: " . json_encode($statuses));
        
        $success = $this->memberStatusModel->updateSortOrders($statuses);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Sort order updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update sort order']);
        }
    }
}


