<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ChurchModel;
use App\Models\UserModel;

class ChurchController extends Controller
{
    private ChurchModel $churchModel;
    private UserModel $userModel;
    
    public function __construct()
    {
        $this->churchModel = new ChurchModel();
        $this->userModel = new UserModel();
    }
    
    public function index(): void
    {
        $this->requireRole(ROLE_SUPER_ADMIN);
        
        $churches = $this->churchModel->getAllChurches();
        $this->view('church/index', ['churches' => $churches]);
    }
    
    public function create(): void
    {
        $this->requireRole(ROLE_SUPER_ADMIN);
        
        $pastors = $this->userModel->getUsersByRole(ROLE_PASTOR);
        $this->view('church/create', ['pastors' => $pastors]);
    }
    
    public function store(): void
    {
        $this->requireRole(ROLE_SUPER_ADMIN);
        
        $data = [
            'name' => $_POST['name'] ?? '',
            'address' => $_POST['address'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'email' => $_POST['email'] ?? '',
            'pastor_id' => $_POST['pastor_id'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        if (empty($data['name'])) {
            flash('Church name is required', 'error');
            $this->view('church/create', ['data' => $data]);
            return;
        }
        
        $churchId = $this->churchModel->create($data);
        
        if ($churchId) {
            // Update the pastor's church_id if a pastor is assigned
            if (!empty($data['pastor_id'])) {
                $this->userModel->update($data['pastor_id'], ['church_id' => $churchId]);
            }
            
            flash('Church created successfully', 'success');
            $this->redirect('/church');
        } else {
            flash('Failed to create church', 'error');
            $this->view('church/create', ['data' => $data]);
        }
    }
    
    public function edit(string $id): void
    {
        $this->requireRole(ROLE_SUPER_ADMIN);
        
        $church = $this->churchModel->findById((int) $id);
        $pastors = $this->userModel->getUsersByRole(ROLE_PASTOR);
        
        if (!$church) {
            flash('Church not found', 'error');
            $this->redirect('/church');
        }
        
        $this->view('church/edit', ['church' => $church, 'pastors' => $pastors]);
    }
    
    public function update(string $id): void
    {
        $this->requireRole(ROLE_SUPER_ADMIN);
        
        $churchId = (int) $id;
        $church = $this->churchModel->findById($churchId);
        
        if (!$church) {
            flash('Church not found', 'error');
            $this->redirect('/church');
        }
        
        $data = [
            'name' => $_POST['name'] ?? '',
            'address' => $_POST['address'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'email' => $_POST['email'] ?? '',
            'pastor_id' => $_POST['pastor_id'] ?? null,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($this->churchModel->update($churchId, $data)) {
            // Update the pastor's church_id if pastor assignment changed
            if ($data['pastor_id'] != $church['pastor_id']) {
                // Clear the old pastor's church_id if there was one
                if (!empty($church['pastor_id'])) {
                    $this->userModel->update($church['pastor_id'], ['church_id' => null]);
                }
                
                // Set the new pastor's church_id if one is assigned
                if (!empty($data['pastor_id'])) {
                    $this->userModel->update($data['pastor_id'], ['church_id' => $churchId]);
                }
            }
            
            flash('Church updated successfully', 'success');
            $this->redirect('/church');
        } else {
            flash('Failed to update church', 'error');
            $this->view('church/edit', ['church' => $church]);
        }
    }
    
    public function delete(string $id): void
    {
        $this->requireRole(ROLE_SUPER_ADMIN);
        
        $churchId = (int) $id;
        $church = $this->churchModel->findById($churchId);
        
        if ($this->churchModel->delete($churchId)) {
            // Clear the pastor's church_id if this church had a pastor assigned
            if (!empty($church['pastor_id'])) {
                $this->userModel->update($church['pastor_id'], ['church_id' => null]);
            }
            
            flash('Church deleted successfully', 'success');
        } else {
            flash('Failed to delete church', 'error');
        }
        
        $this->redirect('/church');
    }
    
    public function fixPastorAssignments(): void
    {
        $this->requireRole(ROLE_SUPER_ADMIN);
        
        // Get all pastors with church assignments
        $db = \App\Core\Database::getInstance();
        $sql = "SELECT id, name, church_id FROM users WHERE role = 'pastor' AND church_id IS NOT NULL";
        $pastors = $db->fetchAll($sql);
        $updatedCount = 0;
        
        foreach ($pastors as $pastor) {
            if (!empty($pastor['church_id'])) {
                // Update the church's pastor_id
                if ($this->churchModel->updatePastorId((int)$pastor['church_id'], (int)$pastor['id'])) {
                    $updatedCount++;
                }
            }
        }
        
        flash("Fixed pastor assignments for {$updatedCount} churches", 'success');
        $this->redirect('/church');
    }
} 