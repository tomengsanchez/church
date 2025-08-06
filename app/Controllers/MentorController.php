<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserModel;

class MentorController extends Controller
{
    private UserModel $userModel;
    
    public function __construct()
    {
        $this->userModel = new UserModel();
    }
    
    public function index(): void
    {
        $this->requireRole(ROLE_COACH);
        
        $mentors = $this->userModel->getMentorsWithChurchesAndPastors();
        $this->view('mentor/index', ['mentors' => $mentors]);
    }
    
    public function create(): void
    {
        $this->requireRole(ROLE_COACH);
        
        $churchModel = new \App\Models\ChurchModel();
        $churches = $churchModel->getAllChurches();
        
        $this->view('mentor/create', ['churches' => $churches]);
    }
    
    public function store(): void
    {
        $this->requireRole(ROLE_COACH);
        
        $data = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'address' => $_POST['address'] ?? '',
            'role' => 'mentor',
            'church_id' => $_POST['church_id'] ?? null,
            'status' => 'active'
        ];
        
        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            flash('Name, email and password are required', 'error');
            $this->view('mentor/create', ['data' => $data]);
            return;
        }
        
        if ($this->userModel->findByEmail($data['email'])) {
            flash('Email already exists', 'error');
            $this->view('mentor/create', ['data' => $data]);
            return;
        }
        
        $userId = $this->userModel->create($data);
        
        if ($userId) {
            // Create hierarchy relationship with coach if selected
            if (!empty($_POST['coach_id'])) {
                $this->userModel->createHierarchyRelationship($userId, (int)$_POST['coach_id']);
            }
            
            flash('Mentor created successfully', 'success');
            $this->redirect('/mentor');
        } else {
            flash('Failed to create mentor', 'error');
            $this->view('mentor/create', ['data' => $data]);
        }
    }
    
    public function edit(string $id): void
    {
        $this->requireRole(ROLE_COACH);
        
        $mentor = $this->userModel->findById((int) $id);
        
        if (!$mentor || $mentor['role'] !== 'mentor') {
            flash('Mentor not found', 'error');
            $this->redirect('/mentor');
        }
        
        $churchModel = new \App\Models\ChurchModel();
        $churches = $churchModel->getAllChurches();
        
        // Get current coach assignment
        $currentCoach = $this->userModel->getHierarchyParent((int) $id);
        
        $this->view('mentor/edit', [
            'mentor' => $mentor, 
            'churches' => $churches,
            'currentCoach' => $currentCoach
        ]);
    }
    
    public function update(string $id): void
    {
        $this->requireRole(ROLE_COACH);
        
        $mentorId = (int) $id;
        $mentor = $this->userModel->findById($mentorId);
        
        if (!$mentor || $mentor['role'] !== 'mentor') {
            flash('Mentor not found', 'error');
            $this->redirect('/mentor');
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
        
        if ($this->userModel->update($mentorId, $data)) {
            // Update hierarchy relationship with coach
            $coachId = !empty($_POST['coach_id']) ? (int)$_POST['coach_id'] : null;
            $this->userModel->updateHierarchyRelationship($mentorId, $coachId);
            
            flash('Mentor updated successfully', 'success');
            $this->redirect('/mentor');
        } else {
            flash('Failed to update mentor', 'error');
            $this->view('mentor/edit', ['mentor' => $mentor]);
        }
    }
    
    public function delete(string $id): void
    {
        $this->requireRole(ROLE_COACH);
        
        $mentorId = (int) $id;
        $mentor = $this->userModel->findById($mentorId);
        
        if (!$mentor || $mentor['role'] !== 'mentor') {
            flash('Mentor not found', 'error');
            $this->redirect('/mentor');
        }
        
        if ($this->userModel->delete($mentorId)) {
            // Clear hierarchy relationships
            $this->userModel->updateHierarchyRelationship($mentorId, null);
            
            flash('Mentor deleted successfully', 'success');
        } else {
            flash('Failed to delete mentor', 'error');
        }
        
        $this->redirect('/mentor');
    }
    
    public function getCoachesByChurch(string $churchId): void
    {
        $this->requireRole(ROLE_COACH);
        
        $coaches = $this->userModel->getCoachesByChurch((int) $churchId);
        
        header('Content-Type: application/json');
        echo json_encode($coaches);
        exit;
    }
} 