<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\MemberModel;
use App\Models\UserModel;
use App\Models\ChurchModel;
use App\Models\LifegroupModel;

class MemberController extends Controller
{
    private MemberModel $memberModel;
    private UserModel $userModel;
    private ChurchModel $churchModel;
    private LifegroupModel $lifegroupModel;
    
    public function __construct()
    {
        $this->memberModel = new MemberModel();
        $this->userModel = new UserModel();
        $this->churchModel = new ChurchModel();
        $this->lifegroupModel = new LifegroupModel();
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
        
        // If filters are applied, use search method (now includes hierarchy)
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
        
        // Always enhance member data with hierarchy information
        $enhancedMembers = [];
        foreach ($members as $member) {
            // Always get full hierarchy data for each member
            $enhancedMember = $this->memberModel->getMemberWithHierarchy($member['id']);
            if ($enhancedMember) {
                $enhancedMembers[] = $enhancedMember;
            } else {
                // Fallback to original member data if enhancement fails
                // Add default values for hierarchy fields
                $member['church_name'] = $member['church_name'] ?? 'Not Assigned';
                $member['pastor_name'] = $member['pastor_name'] ?? 'Not Assigned';
                $member['coach_name'] = $member['coach_name'] ?? 'Not Assigned';
                $member['mentor_name'] = $member['mentor_name'] ?? 'Not Assigned';
                $enhancedMembers[] = $member;
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
        if (empty($data['name'])) {
            flash('Name is required', 'error');
            $this->view('member/create', [
                'data' => array_merge($data, [
                    'coach_id' => $_POST['coach_id'] ?? null,
                    'mentor_id' => $_POST['mentor_id'] ?? null,
                    'lifegroup_id' => $_POST['lifegroup_id'] ?? null
                ]),
                'churches' => $this->churchModel->getAllChurches()
            ]);
            return;
        }
        
        // Validate email if provided
        if (!empty($data['email'])) {
            // Check if email already exists
            if ($this->userModel->findByEmail($data['email'])) {
                flash('Email address already exists', 'error');
                $this->view('member/create', [
                    'data' => array_merge($data, [
                        'coach_id' => $_POST['coach_id'] ?? null,
                        'mentor_id' => $_POST['mentor_id'] ?? null,
                        'lifegroup_id' => $_POST['lifegroup_id'] ?? null
                    ]),
                    'churches' => $this->churchModel->getAllChurches()
                ]);
                return;
            }
        }
        
        // Validate password if provided
        if (!empty($data['password'])) {
            if (strlen($data['password']) < 6) {
                flash('Password must be at least 6 characters long', 'error');
                $this->view('member/create', [
                    'data' => array_merge($data, [
                        'coach_id' => $_POST['coach_id'] ?? null,
                        'mentor_id' => $_POST['mentor_id'] ?? null,
                        'lifegroup_id' => $_POST['lifegroup_id'] ?? null
                    ]),
                    'churches' => $this->churchModel->getAllChurches()
                ]);
                return;
            }
        }
        
        $userId = $this->userModel->create($data);
        
        if ($userId) {
            // Handle mentor assignment (members are assigned to mentors)
            if (!empty($_POST['mentor_id'])) {
                $mentorId = (int)$_POST['mentor_id'];
                $this->userModel->createHierarchyRelationship($userId, $mentorId);
                
                // If coach is also selected, ensure the mentor is assigned to the coach
                if (!empty($_POST['coach_id'])) {
                    $coachId = (int)$_POST['coach_id'];
                    $coachMentor = $this->userModel->getHierarchyParent($mentorId);
                    if (!$coachMentor || $coachMentor['id'] != $coachId) {
                        $this->userModel->updateHierarchyRelationship($mentorId, $coachId);
                    }
                }
            }
            
            // Handle lifegroup assignment
            if (!empty($_POST['lifegroup_id'])) {
                $lifegroupId = (int)$_POST['lifegroup_id'];
                $this->lifegroupModel->addMemberToLifegroup($lifegroupId, $userId);
            }
            
            flash('Member created successfully', 'success');
            $this->redirect('/member');
        } else {
            flash('Failed to create member', 'error');
            $this->view('member/create', [
                'data' => array_merge($data, [
                    'coach_id' => $_POST['coach_id'] ?? null,
                    'mentor_id' => $_POST['mentor_id'] ?? null,
                    'lifegroup_id' => $_POST['lifegroup_id'] ?? null
                ]),
                'churches' => $this->churchModel->getAllChurches()
            ]);
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
        
        // Get current mentor assignment (members are directly assigned to mentors)
        $currentMentor = $this->userModel->getHierarchyParent((int) $id);
        $currentCoach = null;
        
        // If there's a mentor, get the coach through the mentor
        if ($currentMentor && $currentMentor['role'] === 'mentor') {
            $currentCoach = $this->userModel->getHierarchyParent($currentMentor['id']);
        }
        
        // Get current lifegroup assignment
        $currentLifegroup = $this->lifegroupModel->getMemberLifegroup((int) $id);
        
        $this->view('member/edit', [
            'member' => $member, 
            'churches' => $churches,
            'currentCoach' => $currentCoach,
            'currentMentor' => $currentMentor,
            'currentLifegroup' => $currentLifegroup
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
        
        // Validate required fields
        if (empty($data['name'])) {
            flash('Name is required', 'error');
            $this->view('member/edit', [
                'member' => array_merge($member, $data, [
                    'coach_id' => $_POST['coach_id'] ?? null,
                    'mentor_id' => $_POST['mentor_id'] ?? null,
                    'lifegroup_id' => $_POST['lifegroup_id'] ?? null
                ]),
                'churches' => $this->churchModel->getAllChurches(),
                'currentCoach' => null,
                'currentMentor' => null,
                'currentLifegroup' => null
            ]);
            return;
        }
        
        // Validate email if provided
        if (!empty($data['email'])) {
            // Check if email already exists (excluding current user)
            $existingUser = $this->userModel->findByEmail($data['email']);
            if ($existingUser && $existingUser['id'] != $memberId) {
                flash('Email address already exists', 'error');
                $this->view('member/edit', [
                    'member' => array_merge($member, $data, [
                        'coach_id' => $_POST['coach_id'] ?? null,
                        'mentor_id' => $_POST['mentor_id'] ?? null,
                        'lifegroup_id' => $_POST['lifegroup_id'] ?? null
                    ]),
                    'churches' => $this->churchModel->getAllChurches(),
                    'currentCoach' => null,
                    'currentMentor' => null,
                    'currentLifegroup' => null
                ]);
                return;
            }
        }
        
        if ($this->userModel->update($memberId, $data)) {
            // Handle mentor assignment (members are assigned to mentors)
            $mentorId = !empty($_POST['mentor_id']) ? (int)$_POST['mentor_id'] : null;
            $this->userModel->updateHierarchyRelationship($memberId, $mentorId);
            
            // If mentor is selected and coach is also selected, ensure the mentor is assigned to the coach
            if (!empty($_POST['mentor_id']) && !empty($_POST['coach_id'])) {
                $coachId = (int)$_POST['coach_id'];
                $mentorId = (int)$_POST['mentor_id'];
                
                // Check if mentor is already assigned to this coach
                $mentorCoach = $this->userModel->getHierarchyParent($mentorId);
                if (!$mentorCoach || $mentorCoach['id'] != $coachId) {
                    $this->userModel->updateHierarchyRelationship($mentorId, $coachId);
                }
            }
            
            // Handle lifegroup assignment
            if (!empty($_POST['lifegroup_id'])) {
                $lifegroupId = (int)$_POST['lifegroup_id'];
                // First remove from any existing lifegroup
                $this->lifegroupModel->removeMemberFromAllLifegroups($memberId);
                // Then add to the new lifegroup
                $this->lifegroupModel->addMemberToLifegroup($lifegroupId, $memberId);
            } else {
                // If no lifegroup is selected, remove from any existing lifegroup
                $this->lifegroupModel->removeMemberFromAllLifegroups($memberId);
            }
            
            flash('Member updated successfully', 'success');
            $this->redirect('/member');
        } else {
            flash('Failed to update member', 'error');
            $this->view('member/edit', [
                'member' => array_merge($member, $data, [
                    'coach_id' => $_POST['coach_id'] ?? null,
                    'mentor_id' => $_POST['mentor_id'] ?? null,
                    'lifegroup_id' => $_POST['lifegroup_id'] ?? null
                ]),
                'churches' => $this->churchModel->getAllChurches(),
                'currentCoach' => null,
                'currentMentor' => null,
                'currentLifegroup' => null
            ]);
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
    
    public function getLifegroupsByMentor(string $mentorId): void
    {
        $this->requireRole(ROLE_MENTOR);
        
        $lifegroups = $this->lifegroupModel->getLifegroupsByMentor((int) $mentorId);
        
        header('Content-Type: application/json');
        echo json_encode($lifegroups);
        exit;
    }
} 