<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserModel;

class CoachController extends Controller
{
    private UserModel $userModel;
    
    public function __construct()
    {
        $this->userModel = new UserModel();
    }
    
    public function index(): void
    {
        $this->requireRole(ROLE_PASTOR);
        
        $coaches = $this->userModel->getUsersByRole(ROLE_COACH);
        $this->view('coach/index', ['coaches' => $coaches]);
    }
    
    public function create(): void
    {
        $this->requireRole(ROLE_PASTOR);
        
        $this->view('coach/create');
    }
    
    public function store(): void
    {
        $this->requireRole(ROLE_PASTOR);
        
        $data = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'address' => $_POST['address'] ?? '',
            'role' => 'coach',
            'church_id' => $_SESSION['church_id'],
            'status' => 'active'
        ];
        
        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            flash('Name, email and password are required', 'error');
            $this->view('coach/create', ['data' => $data]);
            return;
        }
        
        if ($this->userModel->findByEmail($data['email'])) {
            flash('Email already exists', 'error');
            $this->view('coach/create', ['data' => $data]);
            return;
        }
        
        $userId = $this->userModel->createUser($data);
        
        if ($userId) {
            flash('Coach created successfully', 'success');
            $this->redirect('/coach');
        } else {
            flash('Failed to create coach', 'error');
            $this->view('coach/create', ['data' => $data]);
        }
    }
    
    public function edit(string $id): void
    {
        $this->requireRole(ROLE_PASTOR);
        
        $coach = $this->userModel->findById((int) $id);
        
        if (!$coach || $coach['role'] !== 'coach') {
            flash('Coach not found', 'error');
            $this->redirect('/coach');
        }
        
        $this->view('coach/edit', ['coach' => $coach]);
    }
    
    public function update(string $id): void
    {
        $this->requireRole(ROLE_PASTOR);
        
        $coachId = (int) $id;
        $coach = $this->userModel->findById($coachId);
        
        if (!$coach || $coach['role'] !== 'coach') {
            flash('Coach not found', 'error');
            $this->redirect('/coach');
        }
        
        $data = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'address' => $_POST['address'] ?? ''
        ];
        
        if (!empty($_POST['password'])) {
            $data['password'] = $_POST['password'];
        }
        
        if ($this->userModel->updateUser($coachId, $data)) {
            flash('Coach updated successfully', 'success');
            $this->redirect('/coach');
        } else {
            flash('Failed to update coach', 'error');
            $this->view('coach/edit', ['coach' => $coach]);
        }
    }
    
    public function delete(string $id): void
    {
        $this->requireRole(ROLE_PASTOR);
        
        $coachId = (int) $id;
        $coach = $this->userModel->findById($coachId);
        
        if (!$coach || $coach['role'] !== 'coach') {
            flash('Coach not found', 'error');
            $this->redirect('/coach');
        }
        
        if ($this->userModel->delete($coachId)) {
            flash('Coach deleted successfully', 'success');
        } else {
            flash('Failed to delete coach', 'error');
        }
        
        $this->redirect('/coach');
    }
} 