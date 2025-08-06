<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserModel;

class PastorController extends Controller
{
    private UserModel $userModel;
    
    public function __construct()
    {
        $this->userModel = new UserModel();
    }
    
    public function index(): void
    {
        $this->requireRole(ROLE_SUPER_ADMIN);
        
        $pastors = $this->userModel->getPastorsWithChurches();
        $this->view('pastor/index', ['pastors' => $pastors]);
    }
    
    public function create(): void
    {
        $this->requireRole(ROLE_SUPER_ADMIN);
        
        $churchModel = new \App\Models\ChurchModel();
        $churches = $churchModel->getAllChurches();
        
        $this->view('pastor/create', ['churches' => $churches]);
    }
    
    public function store(): void
    {
        $this->requireRole(ROLE_SUPER_ADMIN);
        
        $data = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'address' => $_POST['address'] ?? '',
            'role' => 'pastor',
            'church_id' => $_POST['church_id'] ?? null,
            'status' => 'active'
        ];
        
        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            flash('Name, email and password are required', 'error');
            $this->view('pastor/create', ['data' => $data]);
            return;
        }
        
        if ($this->userModel->findByEmail($data['email'])) {
            flash('Email already exists', 'error');
            $this->view('pastor/create', ['data' => $data]);
            return;
        }
        
        $userId = $this->userModel->create($data);
        
        if ($userId) {
            // Update the church's pastor_id if a church is assigned
            if (!empty($data['church_id'])) {
                $churchModel = new \App\Models\ChurchModel();
                $churchModel->updatePastorId((int)$data['church_id'], $userId);
            }
            
            flash('Pastor created successfully', 'success');
            $this->redirect('/pastor');
        } else {
            flash('Failed to create pastor', 'error');
            $this->view('pastor/create', ['data' => $data]);
        }
    }
    
    public function edit(string $id): void
    {
        $this->requireRole(ROLE_SUPER_ADMIN);
        
        $pastor = $this->userModel->findById((int) $id);
        
        if (!$pastor || $pastor['role'] !== 'pastor') {
            flash('Pastor not found', 'error');
            $this->redirect('/pastor');
        }
        
        $churchModel = new \App\Models\ChurchModel();
        $churches = $churchModel->getAllChurches();
        
        $this->view('pastor/edit', ['pastor' => $pastor, 'churches' => $churches]);
    }
    
    public function update(string $id): void
    {
        $this->requireRole(ROLE_SUPER_ADMIN);
        
        $pastorId = (int) $id;
        $pastor = $this->userModel->findById($pastorId);
        
        if (!$pastor || $pastor['role'] !== 'pastor') {
            flash('Pastor not found', 'error');
            $this->redirect('/pastor');
        }
        
        $data = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'address' => $_POST['address'] ?? '',
            'church_id' => $_POST['church_id'] ?? null
        ];
        
        if (!empty($_POST['password'])) {
            $data['password'] = $_POST['password'];
        }
        
        if ($this->userModel->update($pastorId, $data)) {
            // Update the church's pastor_id if church assignment changed
            $churchModel = new \App\Models\ChurchModel();
            
            // First, clear the pastor_id from the old church (if any)
            if (!empty($pastor['church_id'])) {
                $churchModel->updatePastorId((int)$pastor['church_id'], null);
            }
            
            // Then, set the pastor_id for the new church (if any)
            if (!empty($data['church_id'])) {
                $churchModel->updatePastorId((int)$data['church_id'], $pastorId);
            }
            
            flash('Pastor updated successfully', 'success');
            $this->redirect('/pastor');
        } else {
            flash('Failed to update pastor', 'error');
            $this->view('pastor/edit', ['pastor' => $pastor]);
        }
    }
    
    public function delete(string $id): void
    {
        $this->requireRole(ROLE_SUPER_ADMIN);
        
        $pastorId = (int) $id;
        $pastor = $this->userModel->findById($pastorId);
        
        if (!$pastor || $pastor['role'] !== 'pastor') {
            flash('Pastor not found', 'error');
            $this->redirect('/pastor');
        }
        
        if ($this->userModel->delete($pastorId)) {
            // Clear the pastor_id from the church if this pastor was assigned to one
            if (!empty($pastor['church_id'])) {
                $churchModel = new \App\Models\ChurchModel();
                $churchModel->updatePastorId((int)$pastor['church_id'], null);
            }
            
            flash('Pastor deleted successfully', 'success');
        } else {
            flash('Failed to delete pastor', 'error');
        }
        
        $this->redirect('/pastor');
    }
} 