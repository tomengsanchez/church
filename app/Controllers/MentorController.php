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
        $this->requireAuth();
        
        $userRole = $this->getUserRole();
        
        // Allow super admins, pastors and coaches to access mentors
        if (!in_array($userRole, [ROLE_SUPER_ADMIN, ROLE_PASTOR, ROLE_COACH])) {
            $this->redirect('/dashboard');
        }
        
        $churchId = $_SESSION['church_id'] ?? null;
        
        // If church_id is not in session, get it from the database
        if ($churchId === null) {
            $user = $this->userModel->findById($_SESSION['user_id']);
            if ($user && isset($user['church_id'])) {
                $churchId = $user['church_id'];
            }
        }
        
        if ($userRole === ROLE_SUPER_ADMIN) {
            // Super admin can see all mentors
            $mentors = $this->userModel->getMentorsWithChurchesAndPastors();
        } elseif ($userRole === ROLE_PASTOR) {
            // Pastors can see all mentors from their church
            if ($churchId) {
                $mentors = $this->userModel->getMentorsWithChurchesAndPastorsByChurch((int)$churchId);
            } else {
                $mentors = [];
            }
        } elseif ($userRole === ROLE_COACH) {
            // Coaches can only see mentors assigned to them
            $mentors = $this->userModel->getMentorsWithChurchesAndPastorsByCoach((int)$_SESSION['user_id']);
        } else {
            $mentors = [];
        }
        
        $this->view('mentor/index', ['mentors' => $mentors]);
    }
    
    public function create(): void
    {
        $this->requireAuth();
        
        $userRole = $this->getUserRole();
        
        // Allow super admins, pastors and coaches to access mentors
        if (!in_array($userRole, [ROLE_SUPER_ADMIN, ROLE_PASTOR, ROLE_COACH])) {
            $this->redirect('/dashboard');
        }
        
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
            // Super admin can create mentors for any church
            $churches = $churchModel->getAllChurches();
        } elseif ($userRole === ROLE_PASTOR || $userRole === ROLE_COACH) {
            // Pastors and coaches can only create mentors for their own church
            if ($churchId) {
                $churches = [$churchModel->findById($churchId)];
            } else {
                $churches = [];
            }
        } else {
            $churches = [];
        }
        
        $this->view('mentor/create', ['churches' => $churches]);
    }
    
    public function store(): void
    {
        $this->requireAuth();
        
        $userRole = $this->getUserRole();
        
        // Allow super admins, pastors and coaches to access mentors
        if (!in_array($userRole, [ROLE_SUPER_ADMIN, ROLE_PASTOR, ROLE_COACH])) {
            $this->redirect('/dashboard');
        }
        
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
            'phone' => $_POST['phone'] ?? '',
            'address' => $_POST['address'] ?? '',
            'role' => 'mentor',
            'church_id' => $_POST['church_id'] ?? null,
            'status' => 'active'
        ];
        
        // Validate that pastors and coaches can only create mentors for their own church
        if ($userRole !== ROLE_SUPER_ADMIN && $data['church_id'] != $churchId) {
            flash('You can only create mentors for your own church', 'error');
            $this->redirect('/mentor/create');
            return;
        }
        
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
        $this->requireAuth();
        
        $userRole = $this->getUserRole();
        
        // Allow super admins, pastors and coaches to access mentors
        if (!in_array($userRole, [ROLE_SUPER_ADMIN, ROLE_PASTOR, ROLE_COACH])) {
            $this->redirect('/dashboard');
        }
        
        $churchId = $_SESSION['church_id'] ?? null;
        
        // If church_id is not in session, get it from the database
        if ($churchId === null) {
            $user = $this->userModel->findById($_SESSION['user_id']);
            if ($user && isset($user['church_id'])) {
                $churchId = $user['church_id'];
            }
        }
        
        $mentor = $this->userModel->findById((int) $id);
        
        if (!$mentor || $mentor['role'] !== 'mentor') {
            flash('Mentor not found', 'error');
            $this->redirect('/mentor');
        }
        
        // Check if pastor/coach can edit this mentor
        if ($userRole === ROLE_PASTOR) {
            // Pastors can only edit mentors from their own church
            if ($mentor['church_id'] != $churchId) {
                flash('You can only edit mentors from your own church', 'error');
                $this->redirect('/mentor');
                return;
            }
        } elseif ($userRole === ROLE_COACH) {
            // Coaches can only edit mentors assigned to them
            $currentCoach = $this->userModel->getHierarchyParent((int) $id);
            if (!$currentCoach || $currentCoach['id'] != $_SESSION['user_id']) {
                flash('You can only edit mentors assigned to you', 'error');
                $this->redirect('/mentor');
                return;
            }
        }
        
        $churchModel = new \App\Models\ChurchModel();
        
        if ($userRole === ROLE_SUPER_ADMIN) {
            // Super admin can edit mentors for any church
            $churches = $churchModel->getAllChurches();
        } elseif ($userRole === ROLE_PASTOR || $userRole === ROLE_COACH) {
            // Pastors and coaches can only edit mentors for their own church
            if ($churchId) {
                $churches = [$churchModel->findById($churchId)];
            } else {
                $churches = [];
            }
        } else {
            $churches = [];
        }
        
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
        $this->requireAuth();
        
        $userRole = $this->getUserRole();
        
        // Allow super admins, pastors and coaches to access mentors
        if (!in_array($userRole, [ROLE_SUPER_ADMIN, ROLE_PASTOR, ROLE_COACH])) {
            $this->redirect('/dashboard');
        }
        
        $churchId = $_SESSION['church_id'] ?? null;
        
        // If church_id is not in session, get it from the database
        if ($churchId === null) {
            $user = $this->userModel->findById($_SESSION['user_id']);
            if ($user && isset($user['church_id'])) {
                $churchId = $user['church_id'];
            }
        }
        
        $mentorId = (int) $id;
        $mentor = $this->userModel->findById($mentorId);
        
        if (!$mentor || $mentor['role'] !== 'mentor') {
            flash('Mentor not found', 'error');
            $this->redirect('/mentor');
        }
        
        // Check if pastor/coach can edit this mentor
        if ($userRole === ROLE_PASTOR) {
            // Pastors can only edit mentors from their own church
            if ($mentor['church_id'] != $churchId) {
                flash('You can only edit mentors from your own church', 'error');
                $this->redirect('/mentor');
                return;
            }
        } elseif ($userRole === ROLE_COACH) {
            // Coaches can only edit mentors assigned to them
            $currentCoach = $this->userModel->getHierarchyParent((int) $id);
            if (!$currentCoach || $currentCoach['id'] != $_SESSION['user_id']) {
                flash('You can only edit mentors assigned to you', 'error');
                $this->redirect('/mentor');
                return;
            }
        }
        
        $data = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'address' => $_POST['address'] ?? '',
            'church_id' => $_POST['church_id'] ?? null
        ];
        
        // Validate that pastors and coaches can only update mentors for their own church
        if ($userRole === ROLE_PASTOR && $data['church_id'] != $churchId) {
            flash('You can only update mentors for your own church', 'error');
            $this->redirect('/mentor/edit/' . $mentorId);
            return;
        } elseif ($userRole === ROLE_COACH) {
            // Coaches can only update mentors assigned to them (church_id should be the same)
            if ($data['church_id'] != $churchId) {
                flash('You can only update mentors for your own church', 'error');
                $this->redirect('/mentor/edit/' . $mentorId);
                return;
            }
        }
        
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
        $this->requireAuth();
        
        $userRole = $this->getUserRole();
        
        // Allow super admins, pastors and coaches to access mentors
        if (!in_array($userRole, [ROLE_SUPER_ADMIN, ROLE_PASTOR, ROLE_COACH])) {
            $this->redirect('/dashboard');
        }
        
        $churchId = $_SESSION['church_id'] ?? null;
        
        // If church_id is not in session, get it from the database
        if ($churchId === null) {
            $user = $this->userModel->findById($_SESSION['user_id']);
            if ($user && isset($user['church_id'])) {
                $churchId = $user['church_id'];
            }
        }
        
        $mentorId = (int) $id;
        $mentor = $this->userModel->findById($mentorId);
        
        if (!$mentor || $mentor['role'] !== 'mentor') {
            flash('Mentor not found', 'error');
            $this->redirect('/mentor');
        }
        
        // Check if pastor/coach can delete this mentor
        if ($userRole === ROLE_PASTOR) {
            // Pastors can only delete mentors from their own church
            if ($mentor['church_id'] != $churchId) {
                flash('You can only delete mentors from your own church', 'error');
                $this->redirect('/mentor');
                return;
            }
        } elseif ($userRole === ROLE_COACH) {
            // Coaches can only delete mentors assigned to them
            $currentCoach = $this->userModel->getHierarchyParent((int) $id);
            if (!$currentCoach || $currentCoach['id'] != $_SESSION['user_id']) {
                flash('You can only delete mentors assigned to you', 'error');
                $this->redirect('/mentor');
                return;
            }
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
        $this->requireAuth();
        
        $userRole = $this->getUserRole();
        
        // Allow super admins, pastors and coaches to access mentors
        if (!in_array($userRole, [ROLE_SUPER_ADMIN, ROLE_PASTOR, ROLE_COACH])) {
            $this->redirect('/dashboard');
        }
        
        $coaches = $this->userModel->getCoachesByChurch((int) $churchId);
        
        header('Content-Type: application/json');
        echo json_encode($coaches);
        exit;
    }
} 