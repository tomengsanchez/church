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
        
        $userRole = $this->getUserRole();
        $churchId = $_SESSION['church_id'] ?? null;
        
        // If church_id is not in session, get it from the database
        if ($churchId === null) {
            $user = $this->userModel->findById($_SESSION['user_id']);
            if ($user && isset($user['church_id'])) {
                $churchId = $user['church_id'];
            }
        }
        
        if ($userRole === ROLE_SUPER_ADMIN) {
            // Super admin can see all coaches
            $coaches = $this->userModel->getCoachesWithChurchesAndPastors();
        } else {
            // Pastors can only see coaches from their church
            if ($churchId) {
                $coaches = $this->userModel->getCoachesWithChurchesAndPastorsByChurch((int)$churchId);
            } else {
                $coaches = [];
            }
        }
        
        $this->view('coach/index', ['coaches' => $coaches]);
    }
    
    public function create(): void
    {
        $this->requireRole(ROLE_PASTOR);
        
        $userRole = $this->getUserRole();
        $churchId = $_SESSION['church_id'] ?? null;
        
        // If church_id is not in session, get it from the database
        if ($churchId === null) {
            $user = $this->userModel->findById($_SESSION['user_id']);
            if ($user && isset($user['church_id'])) {
                $churchId = $user['church_id'];
            }
        }
        
        $churchModel = new \App\Models\ChurchModel();
        
        if ($userRole === ROLE_SUPER_ADMIN) {
            // Super admin can create coaches for any church
            $churches = $churchModel->getAllChurches();
        } else {
            // Pastors can only create coaches for their own church
            if ($churchId) {
                $churches = [$churchModel->findById($churchId)];
            } else {
                $churches = [];
            }
        }
        
        $this->view('coach/create', ['churches' => $churches]);
    }
    
    public function store(): void
    {
        $this->requireRole(ROLE_PASTOR);
        
        $userRole = $this->getUserRole();
        $churchId = $_SESSION['church_id'] ?? null;
        
        // If church_id is not in session, get it from the database
        if ($churchId === null) {
            $user = $this->userModel->findById($_SESSION['user_id']);
            if ($user && isset($user['church_id'])) {
                $churchId = $user['church_id'];
            }
        }
        
        $data = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? '',
            'satelife_name' => $_POST['satelife_name'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'address' => $_POST['address'] ?? '',
            'role' => 'coach',
            'church_id' => $_POST['church_id'] ?? null,
            'status' => 'active'
        ];
        
        // Validate that pastors can only create coaches for their own church
        if ($userRole !== ROLE_SUPER_ADMIN && $data['church_id'] != $churchId) {
            flash('You can only create coaches for your own church', 'error');
            $this->redirect('/coach/create');
            return;
        }
        
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
        
        $userId = $this->userModel->create($data);
        
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
        
        $userRole = $this->getUserRole();
        $churchId = $_SESSION['church_id'] ?? null;
        
        // If church_id is not in session, get it from the database
        if ($churchId === null) {
            $user = $this->userModel->findById($_SESSION['user_id']);
            if ($user && isset($user['church_id'])) {
                $churchId = $user['church_id'];
            }
        }
        
        $coach = $this->userModel->findById((int) $id);
        
        if (!$coach || $coach['role'] !== 'coach') {
            flash('Coach not found', 'error');
            $this->redirect('/coach');
        }
        
        // Check if pastor can edit this coach
        if ($userRole !== ROLE_SUPER_ADMIN && $coach['church_id'] != $churchId) {
            flash('You can only edit coaches from your own church', 'error');
            $this->redirect('/coach');
            return;
        }
        
        $churchModel = new \App\Models\ChurchModel();
        
        if ($userRole === ROLE_SUPER_ADMIN) {
            // Super admin can edit coaches for any church
            $churches = $churchModel->getAllChurches();
        } else {
            // Pastors can only edit coaches for their own church
            if ($churchId) {
                $churches = [$churchModel->findById($churchId)];
            } else {
                $churches = [];
            }
        }
        
        $this->view('coach/edit', ['coach' => $coach, 'churches' => $churches]);
    }
    
    public function update(string $id): void
    {
        $this->requireRole(ROLE_PASTOR);
        
        $userRole = $this->getUserRole();
        $churchId = $_SESSION['church_id'] ?? null;
        
        // If church_id is not in session, get it from the database
        if ($churchId === null) {
            $user = $this->userModel->findById($_SESSION['user_id']);
            if ($user && isset($user['church_id'])) {
                $churchId = $user['church_id'];
            }
        }
        
        $coachId = (int) $id;
        $coach = $this->userModel->findById($coachId);
        
        if (!$coach || $coach['role'] !== 'coach') {
            flash('Coach not found', 'error');
            $this->redirect('/coach');
        }
        
        // Check if pastor can edit this coach
        if ($userRole !== ROLE_SUPER_ADMIN && $coach['church_id'] != $churchId) {
            flash('You can only edit coaches from your own church', 'error');
            $this->redirect('/coach');
            return;
        }
        
        $data = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'satelife_name' => $_POST['satelife_name'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'address' => $_POST['address'] ?? '',
            'church_id' => $_POST['church_id'] ?? null
        ];
        
        // Validate that pastors can only update coaches for their own church
        if ($userRole !== ROLE_SUPER_ADMIN && $data['church_id'] != $churchId) {
            flash('You can only update coaches for your own church', 'error');
            $this->redirect('/coach/edit/' . $coachId);
            return;
        }
        
        if (!empty($_POST['password'])) {
            $data['password'] = $_POST['password'];
        }
        
        if ($this->userModel->update($coachId, $data)) {
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
        
        $userRole = $this->getUserRole();
        $churchId = $_SESSION['church_id'] ?? null;
        
        // If church_id is not in session, get it from the database
        if ($churchId === null) {
            $user = $this->userModel->findById($_SESSION['user_id']);
            if ($user && isset($user['church_id'])) {
                $churchId = $user['church_id'];
            }
        }
        
        $coachId = (int) $id;
        $coach = $this->userModel->findById($coachId);
        
        if (!$coach || $coach['role'] !== 'coach') {
            flash('Coach not found', 'error');
            $this->redirect('/coach');
        }
        
        // Check if pastor can delete this coach
        if ($userRole !== ROLE_SUPER_ADMIN && $coach['church_id'] != $churchId) {
            flash('You can only delete coaches from your own church', 'error');
            $this->redirect('/coach');
            return;
        }
        
        if ($this->userModel->delete($coachId)) {
            flash('Coach deleted successfully', 'success');
        } else {
            flash('Failed to delete coach', 'error');
        }
        
        $this->redirect('/coach');
    }
} 