<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\MemberModel;
use App\Models\UserModel;
use App\Models\ChurchModel;

class MemberController extends Controller
{
    private MemberModel $memberModel;
    private UserModel $userModel;
    private ChurchModel $churchModel;
    
    public function __construct()
    {
        $this->memberModel = new MemberModel();
        $this->userModel = new UserModel();
        $this->churchModel = new ChurchModel();
    }
    
    public function index(): void
    {
        $this->requireAuth(); // Allow any authenticated user to view members
        
        $churchId = $_SESSION['church_id'] ?? null;
        $userRole = $this->getUserRole();
        
        // Get filter parameters
        $status = $_GET['status'] ?? '';
        $churchFilter = $_GET['church_id'] ?? '';
        $search = $_GET['search'] ?? '';
        
        $members = [];
        
        // If filters are applied, use search method
        if (!empty($status) || !empty($churchFilter) || !empty($search)) {
            $members = $this->memberModel->searchMembers($search, $churchFilter ? (int)$churchFilter : null);
            if (!empty($status)) {
                $members = array_filter($members, function($member) use ($status) {
                    return $member['status'] === $status;
                });
            }
        } else {
            // Get members based on user role
            switch ($userRole) {
                case ROLE_SUPER_ADMIN:
                    // Super admin can see all members with hierarchy
                    $members = $this->memberModel->getAllMembersWithHierarchy();
                    break;
                case ROLE_PASTOR:
                    $members = $this->memberModel->getMembersByPastor($_SESSION['user_id']);
                    break;
                case ROLE_COACH:
                    $members = $this->memberModel->getMembersByCoach($_SESSION['user_id']);
                    break;
                case ROLE_MENTOR:
                    $members = $this->memberModel->getMembersByMentor($_SESSION['user_id']);
                    break;
                default:
                    $members = [];
            }
        }
        
        // Enhance member data with hierarchy information (only if not already enhanced)
        $enhancedMembers = [];
        if ($userRole === ROLE_SUPER_ADMIN && empty($status) && empty($churchFilter) && empty($search)) {
            // Super admin already has enhanced data
            $enhancedMembers = $members;
        } else {
            foreach ($members as $member) {
                $enhancedMembers[] = $this->memberModel->getMemberWithHierarchy($member['id']);
            }
        }
        
        $this->view('member/index', [
            'members' => $enhancedMembers,
            'churches' => $this->churchModel->getAllChurches(),
            'stats' => $this->memberModel->getMemberStats($churchId)
        ]);
    }
    
    public function create(): void
    {
        $this->requireRole(ROLE_MENTOR);
        
        $churches = $this->churchModel->getAllChurches();
        
        $this->view('member/create', ['churches' => $churches]);
    }
    
    public function store(): void
    {
        $this->requireRole(ROLE_MENTOR);
        
        $data = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'address' => $_POST['address'] ?? '',
            'church_id' => $_POST['church_id'] ?? null,
            'status' => $_POST['status'] ?? 'active',
            'role' => 'member'
        ];
        
        // Validate required fields
        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            flash('Name, email, and password are required', 'error');
            $this->view('member/create', ['data' => $data]);
            return;
        }
        
        // Validate password length
        if (strlen($data['password']) < 6) {
            flash('Password must be at least 6 characters long', 'error');
            $this->view('member/create', ['data' => $data]);
            return;
        }
        
        // Check if email already exists
        if ($this->userModel->findByEmail($data['email'])) {
            flash('Email address already exists', 'error');
            $this->view('member/create', ['data' => $data]);
            return;
        }
        
        $userId = $this->userModel->create($data);
        
        if ($userId) {
            // Handle coach assignment
            if (!empty($_POST['coach_id'])) {
                $this->userModel->createHierarchyRelationship($userId, (int)$_POST['coach_id']);
            }
            
            // Handle mentor assignment (only if coach is selected)
            if (!empty($_POST['mentor_id']) && !empty($_POST['coach_id'])) {
                $coachId = (int)$_POST['coach_id'];
                $mentorId = (int)$_POST['mentor_id'];
                
                // Check if coach is already assigned to this mentor
                $coachMentor = $this->userModel->getHierarchyParent($coachId);
                if (!$coachMentor || $coachMentor['id'] != $mentorId) {
                    $this->userModel->updateHierarchyRelationship($coachId, $mentorId);
                }
            }
            
            flash('Member created successfully', 'success');
            $this->redirect('/member');
        } else {
            flash('Failed to create member', 'error');
            $this->view('member/create', ['data' => $data]);
        }
    }
    
    public function edit(string $id): void
    {
        $this->requireRole(ROLE_MENTOR);
        
        $member = $this->userModel->findById((int) $id);
        
        if (!$member || $member['role'] !== 'member') {
            flash('Member not found', 'error');
            $this->redirect('/member');
        }
        
        $churches = $this->churchModel->getAllChurches();
        
        // Get current coach and mentor assignments
        $currentCoach = $this->userModel->getHierarchyParent((int) $id);
        $currentMentor = null;
        
        // If there's a coach, get the mentor through the coach
        if ($currentCoach && $currentCoach['role'] === 'coach') {
            $currentMentor = $this->userModel->getHierarchyParent($currentCoach['id']);
        }
        
        $this->view('member/edit', [
            'member' => $member, 
            'churches' => $churches,
            'currentCoach' => $currentCoach,
            'currentMentor' => $currentMentor
        ]);
    }
    
    public function update(string $id): void
    {
        $this->requireRole(ROLE_MENTOR);
        
        $memberId = (int) $id;
        $member = $this->userModel->findById($memberId);
        
        if (!$member || $member['role'] !== 'member') {
            flash('Member not found', 'error');
            $this->redirect('/member');
        }
        
        $data = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'address' => $_POST['address'] ?? '',
            'church_id' => $_POST['church_id'] ?? null,
            'status' => $_POST['status'] ?? 'active'
        ];
        
        if (!empty($_POST['password'])) {
            $data['password'] = $_POST['password'];
        }
        
        if ($this->userModel->update($memberId, $data)) {
            // Handle coach assignment
            $coachId = !empty($_POST['coach_id']) ? (int)$_POST['coach_id'] : null;
            $this->userModel->updateHierarchyRelationship($memberId, $coachId);
            
            // Handle mentor assignment (only if coach is selected)
            if (!empty($_POST['mentor_id']) && !empty($_POST['coach_id'])) {
                $mentorId = (int)$_POST['mentor_id'];
                $coachId = (int)$_POST['coach_id'];
                
                // Check if coach is already assigned to this mentor
                $coachMentor = $this->userModel->getHierarchyParent($coachId);
                if (!$coachMentor || $coachMentor['id'] != $mentorId) {
                    $this->userModel->updateHierarchyRelationship($coachId, $mentorId);
                }
            }
            
            flash('Member updated successfully', 'success');
            $this->redirect('/member');
        } else {
            flash('Failed to update member', 'error');
            $this->view('member/edit', ['member' => $member]);
        }
    }
    
    public function delete(string $id): void
    {
        $this->requireRole(ROLE_MENTOR);
        
        $memberId = (int) $id;
        $member = $this->userModel->findById($memberId);
        
        if (!$member || $member['role'] !== 'member') {
            flash('Member not found', 'error');
            $this->redirect('/member');
        }
        
        if ($this->userModel->delete($memberId)) {
            // Clear hierarchy relationships
            $this->userModel->updateHierarchyRelationship($memberId, null);
            
            flash('Member deleted successfully', 'success');
        } else {
            flash('Failed to delete member', 'error');
        }
        
        $this->redirect('/member');
    }
    
    public function updateStatus(string $id): void
    {
        $this->requireRole(ROLE_MENTOR);
        
        $memberId = (int) $id;
        $status = $_POST['status'] ?? '';
        
        if (!in_array($status, [STATUS_ACTIVE, STATUS_INACTIVE, STATUS_PENDING, STATUS_SUSPENDED])) {
            flash('Invalid status', 'error');
            $this->redirect('/member');
        }
        
        if ($this->memberModel->updateMemberStatus($memberId, $status)) {
            flash('Member status updated successfully', 'success');
        } else {
            flash('Failed to update member status', 'error');
        }
        
        $this->redirect('/member');
    }
    
    public function getCoachesByChurch(string $churchId): void
    {
        $this->requireRole(ROLE_MENTOR);
        
        $coaches = $this->userModel->getCoachesByChurch((int) $churchId);
        
        header('Content-Type: application/json');
        echo json_encode($coaches);
        exit;
    }
    
    public function getMentorsByChurch(string $churchId): void
    {
        $this->requireRole(ROLE_MENTOR);
        
        $mentors = $this->userModel->getMentorsByChurch((int) $churchId);
        
        header('Content-Type: application/json');
        echo json_encode($mentors);
        exit;
    }
    
    public function getMentorsByCoach(string $coachId): void
    {
        $this->requireRole(ROLE_MENTOR);
        
        $mentors = $this->userModel->getMentorsByCoach((int) $coachId);
        
        header('Content-Type: application/json');
        echo json_encode($mentors);
        exit;
    }
} 