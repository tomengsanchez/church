<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\LifegroupModel;
use App\Models\ChurchModel;
use App\Models\UserModel;

class LifegroupController extends Controller
{
    private LifegroupModel $lifegroupModel;
    private ChurchModel $churchModel;
    private UserModel $userModel;
    
    public function __construct()
    {
        $this->lifegroupModel = new LifegroupModel();
        $this->churchModel = new ChurchModel();
        $this->userModel = new UserModel();
    }
    
    public function index(): void
    {
        $this->requireAuth();
        
        $userRole = $this->getUserRole();
        $userId = $_SESSION['user_id'];
        $churchId = $_SESSION['church_id'] ?? null;
        
        // If church_id is not in session, get it from the database
        if ($churchId === null) {
            $user = $this->userModel->findById($userId);
            if ($user && isset($user['church_id'])) {
                $churchId = $user['church_id'];
            }
        }
        
        $lifegroups = [];
        
        if ($userRole === ROLE_SUPER_ADMIN) {
            // Super admin can see all lifegroups
            $lifegroups = $this->lifegroupModel->getAllLifegroups();
        } elseif ($userRole === ROLE_PASTOR) {
            // Pastors can see lifegroups from their church
            if ($churchId) {
                $lifegroups = $this->lifegroupModel->getLifegroupsByChurch((int)$churchId);
            }
        } elseif ($userRole === ROLE_COACH) {
            // Coaches can only see lifegroups managed by mentors assigned to them
            $lifegroups = $this->lifegroupModel->getLifegroupsByCoach($userId);
        } elseif ($userRole === ROLE_MENTOR) {
            // Mentors can see their own lifegroups
            $lifegroups = $this->lifegroupModel->getLifegroupsByMentor($userId);
        } else {
            // Other roles can see lifegroups from their church
            if ($churchId) {
                $lifegroups = $this->lifegroupModel->getLifegroupsByChurch((int)$churchId);
            }
        }
        
        $this->view('lifegroup/index', [
            'title' => 'Lifegroups',
            'lifegroups' => $lifegroups
        ]);
    }
    
    public function create(): void
    {
        $this->requireAuth();
        
        $userRole = $this->getUserRole();
        
        // Allow super admins, pastors, coaches and mentors to create lifegroups
        if (!in_array($userRole, [ROLE_SUPER_ADMIN, ROLE_PASTOR, ROLE_COACH, ROLE_MENTOR])) {
            $this->redirect('/dashboard');
        }
        
        $userId = $_SESSION['user_id'];
        $churchId = $_SESSION['church_id'] ?? null;
        
        // If church_id is not in session, get it from the database
        if ($churchId === null) {
            $user = $this->userModel->findById($userId);
            if ($user && isset($user['church_id'])) {
                $churchId = $user['church_id'];
            }
        }
        
        // For super admins, allow them to select a church
        if ($userRole === ROLE_SUPER_ADMIN) {
            $churches = $this->churchModel->getAllChurches();
            $mentors = [];
            
                    $this->view('lifegroup/create', [
            'title' => 'Create Lifegroup',
            'mentors' => $mentors,
            'churches' => $churches,
            'data' => [],
            'isSuperAdmin' => true,
            'userRole' => $userRole
        ]);
            return;
        }
        
        // For other roles, church_id is required
        if ($churchId === null) {
            setFlash('error', 'Church assignment is required to create a lifegroup');
            $this->redirect('/lifegroup');
            return;
        }
        
        if ($userRole === ROLE_COACH) {
            // Coaches can only create lifegroups for mentors assigned to them
            $mentors = $this->userModel->getMentorsByCoach($userId);
        } elseif ($userRole === ROLE_MENTOR) {
            // Mentors can see themselves and other mentors under their coach
            $coach = $this->userModel->getHierarchyParent($userId);
            $coachId = $coach ? $coach['id'] : null;
            $mentors = $coachId ? $this->userModel->getMentorsByCoach($coachId) : [];
        } else {
            // Pastors can see all mentors from their church
            $mentors = $this->lifegroupModel->getAvailableMentors((int)$churchId);
        }
        
        $this->view('lifegroup/create', [
            'title' => 'Create Lifegroup',
            'mentors' => $mentors,
            'data' => [],
            'isSuperAdmin' => false,
            'userRole' => $userRole
        ]);
    }
    
    public function store(): void
    {
        $this->requireAuth();
        
        $userRole = $this->getUserRole();
        
        // Allow super admins, pastors, coaches and mentors to create lifegroups
        if (!in_array($userRole, [ROLE_SUPER_ADMIN, ROLE_PASTOR, ROLE_COACH, ROLE_MENTOR])) {
            $this->redirect('/dashboard');
        }
        
        $userId = $_SESSION['user_id'];
        $churchId = $_SESSION['church_id'] ?? null;
        
        // If church_id is not in session, get it from the database
        if ($churchId === null) {
            $user = $this->userModel->findById($userId);
            if ($user && isset($user['church_id'])) {
                $churchId = $user['church_id'];
            }
        }
        
        // For super admins, get church_id from form
        if ($userRole === ROLE_SUPER_ADMIN) {
            $churchId = $_POST['church_id'] ?? null;
        }
        
        $data = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'church_id' => $churchId,
            'mentor_id' => $_POST['mentor_id'] ?? null,
            'meeting_day' => $_POST['meeting_day'] ?? '',
            'meeting_time' => $_POST['meeting_time'] ?? '',
            'meeting_location' => $_POST['meeting_location'] ?? '',
            'max_members' => $_POST['max_members'] ?? 20,
            'status' => 'active'
        ];
        
        // Validation
        if (empty($data['name'])) {
            setFlash('error', 'Name is required');
            $this->redirect('/lifegroup/create');
            return;
        }
        
        if (empty($data['church_id'])) {
            setFlash('error', 'Church is required');
            $this->redirect('/lifegroup/create');
            return;
        }
        
        if (empty($data['mentor_id'])) {
            setFlash('error', 'Mentor is required');
            $this->redirect('/lifegroup/create');
            return;
        }
        
        // Check if coach can create lifegroup for this mentor
        if ($userRole === ROLE_COACH) {
            $mentors = $this->userModel->getMentorsByCoach($userId);
            $mentorIds = array_column($mentors, 'id');
            if (!in_array($data['mentor_id'], $mentorIds)) {
                setFlash('error', 'You can only create lifegroups for mentors assigned to you');
                $this->redirect('/lifegroup/create');
                return;
            }
        }
        
        if ($this->lifegroupModel->create($data)) {
            setFlash('success', 'Lifegroup created successfully');
            $this->redirect('/lifegroup');
        } else {
            setFlash('error', 'Failed to create lifegroup');
            $this->redirect('/lifegroup/create');
        }
    }
    
    public function edit(int $id): void
    {
        $this->requireAuth();
        
        $lifegroup = $this->lifegroupModel->getLifegroupWithDetails($id);
        if (!$lifegroup) {
            setFlash('error', 'Lifegroup not found');
            $this->redirect('/lifegroup');
            return;
        }
        
        // Check permissions
        $userRole = $this->getUserRole();
        $userId = $_SESSION['user_id'];
        
        if ($userRole === ROLE_SUPER_ADMIN) {
            // Super admin can edit any lifegroup
        } elseif ($userRole === ROLE_PASTOR) {
            // Pastors can edit lifegroups from their church
            if ($lifegroup['church_id'] != $_SESSION['church_id']) {
                setFlash('error', 'You do not have permission to edit this lifegroup');
                $this->redirect('/lifegroup');
                return;
            }
        } elseif ($userRole === ROLE_COACH) {
            // Coaches can only edit lifegroups managed by mentors assigned to them
            $mentors = $this->userModel->getMentorsByCoach($userId);
            $mentorIds = array_column($mentors, 'id');
            if (!in_array($lifegroup['mentor_id'], $mentorIds)) {
                setFlash('error', 'You do not have permission to edit this lifegroup');
                $this->redirect('/lifegroup');
                return;
            }
        } elseif ($userRole === ROLE_MENTOR) {
            // Mentors can only edit their own lifegroups
            if ($lifegroup['mentor_id'] != $userId) {
                setFlash('error', 'You do not have permission to edit this lifegroup');
                $this->redirect('/lifegroup');
                return;
            }
        } else {
            setFlash('error', 'You do not have permission to edit this lifegroup');
            $this->redirect('/lifegroup');
            return;
        }
        
        $churchId = $lifegroup['church_id'];
        if ($userRole === ROLE_COACH) {
            // Coaches can only assign mentors assigned to them
            $mentors = $this->userModel->getMentorsByCoach($userId);
        } elseif ($userRole === ROLE_MENTOR) {
            // Mentors can see themselves and other mentors under their coach
            $coach = $this->userModel->getHierarchyParent($userId);
            $coachId = $coach ? $coach['id'] : null;
            $mentors = $coachId ? $this->userModel->getMentorsByCoach($coachId) : [];
        } else {
            // Pastors can see all mentors from their church
            $mentors = $this->lifegroupModel->getAvailableMentors($churchId);
        }
        
        $this->view('lifegroup/edit', [
            'title' => 'Edit Lifegroup',
            'lifegroup' => $lifegroup,
            'mentors' => $mentors,
            'userRole' => $userRole
        ]);
    }
    
    public function update(int $id): void
    {
        $this->requireAuth();
        
        $lifegroup = $this->lifegroupModel->getLifegroupWithDetails($id);
        if (!$lifegroup) {
            setFlash('error', 'Lifegroup not found');
            $this->redirect('/lifegroup');
            return;
        }
        
        // Check permissions
        $userRole = $this->getUserRole();
        $userId = $_SESSION['user_id'];
        
        if ($userRole === ROLE_SUPER_ADMIN) {
            // Super admin can edit any lifegroup
        } elseif ($userRole === ROLE_PASTOR) {
            // Pastors can edit lifegroups from their church
            if ($lifegroup['church_id'] != $_SESSION['church_id']) {
                setFlash('error', 'You do not have permission to edit this lifegroup');
                $this->redirect('/lifegroup');
                return;
            }
        } elseif ($userRole === ROLE_COACH) {
            // Coaches can only edit lifegroups managed by mentors assigned to them
            $mentors = $this->userModel->getMentorsByCoach($userId);
            $mentorIds = array_column($mentors, 'id');
            if (!in_array($lifegroup['mentor_id'], $mentorIds)) {
                setFlash('error', 'You do not have permission to edit this lifegroup');
                $this->redirect('/lifegroup');
                return;
            }
        } elseif ($userRole === ROLE_MENTOR) {
            // Mentors can only edit their own lifegroups
            if ($lifegroup['mentor_id'] != $userId) {
                setFlash('error', 'You do not have permission to edit this lifegroup');
                $this->redirect('/lifegroup');
                return;
            }
        } else {
            setFlash('error', 'You do not have permission to edit this lifegroup');
            $this->redirect('/lifegroup');
            return;
        }
        
        $data = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'mentor_id' => $_POST['mentor_id'] ?? null,
            'meeting_day' => $_POST['meeting_day'] ?? '',
            'meeting_time' => $_POST['meeting_time'] ?? '',
            'meeting_location' => $_POST['meeting_location'] ?? '',
            'max_members' => $_POST['max_members'] ?? 20,
            'status' => $_POST['status'] ?? 'active'
        ];
        
        // Validation
        if (empty($data['name'])) {
            setFlash('error', 'Name is required');
            $this->redirect("/lifegroup/edit/{$id}");
            return;
        }
        
        if (empty($data['mentor_id'])) {
            setFlash('error', 'Mentor is required');
            $this->redirect("/lifegroup/edit/{$id}");
            return;
        }
        
        // Check if coach can assign this mentor
        if ($userRole === ROLE_COACH) {
            $mentors = $this->userModel->getMentorsByCoach($userId);
            $mentorIds = array_column($mentors, 'id');
            if (!in_array($data['mentor_id'], $mentorIds)) {
                setFlash('error', 'You can only assign mentors assigned to you');
                $this->redirect("/lifegroup/edit/{$id}");
                return;
            }
        }
        
        if ($this->lifegroupModel->update($id, $data)) {
            setFlash('success', 'Lifegroup updated successfully');
            $this->redirect('/lifegroup');
        } else {
            setFlash('error', 'Failed to update lifegroup');
            $this->redirect("/lifegroup/edit/{$id}");
        }
    }
    
    public function delete(int $id): void
    {
        $this->requireAuth();
        
        $lifegroup = $this->lifegroupModel->getLifegroupWithDetails($id);
        if (!$lifegroup) {
            setFlash('error', 'Lifegroup not found');
            $this->redirect('/lifegroup');
            return;
        }
        
        // Check permissions
        $userRole = $this->getUserRole();
        $userId = $_SESSION['user_id'];
        
        if ($userRole === ROLE_SUPER_ADMIN) {
            // Super admin can delete any lifegroup
        } elseif ($userRole === ROLE_PASTOR) {
            // Pastors can delete lifegroups from their church
            if ($lifegroup['church_id'] != $_SESSION['church_id']) {
                setFlash('error', 'You do not have permission to delete this lifegroup');
                $this->redirect('/lifegroup');
                return;
            }
        } elseif ($userRole === ROLE_COACH) {
            // Coaches can only delete lifegroups managed by mentors assigned to them
            $mentors = $this->userModel->getMentorsByCoach($userId);
            $mentorIds = array_column($mentors, 'id');
            if (!in_array($lifegroup['mentor_id'], $mentorIds)) {
                setFlash('error', 'You do not have permission to delete this lifegroup');
                $this->redirect('/lifegroup');
                return;
            }
        } elseif ($userRole === ROLE_MENTOR) {
            // Mentors can only delete their own lifegroups
            if ($lifegroup['mentor_id'] != $userId) {
                setFlash('error', 'You do not have permission to delete this lifegroup');
                $this->redirect('/lifegroup');
                return;
            }
        } else {
            setFlash('error', 'You do not have permission to delete this lifegroup');
            $this->redirect('/lifegroup');
            return;
        }
        
        if ($this->lifegroupModel->delete($id)) {
            setFlash('success', 'Lifegroup deleted successfully');
        } else {
            setFlash('error', 'Failed to delete lifegroup');
        }
        
        $this->redirect('/lifegroup');
    }
    
    public function show(int $id): void
    {
        $this->requireAuth();
        
        $lifegroup = $this->lifegroupModel->getLifegroupWithDetails($id);
        if (!$lifegroup) {
            setFlash('error', 'Lifegroup not found');
            $this->redirect('/lifegroup');
            return;
        }
        
        // Check permissions
        $userRole = $this->getUserRole();
        $userId = $_SESSION['user_id'];
        
        if ($userRole === ROLE_SUPER_ADMIN) {
            // Super admin can view any lifegroup
        } elseif ($userRole === ROLE_PASTOR) {
            // Pastors can view lifegroups from their church
            if ($lifegroup['church_id'] != $_SESSION['church_id']) {
                setFlash('error', 'You do not have permission to view this lifegroup');
                $this->redirect('/lifegroup');
                return;
            }
        } elseif ($userRole === ROLE_COACH) {
            // Coaches can only view lifegroups managed by mentors assigned to them
            $mentors = $this->userModel->getMentorsByCoach($userId);
            $mentorIds = array_column($mentors, 'id');
            if (!in_array($lifegroup['mentor_id'], $mentorIds)) {
                setFlash('error', 'You do not have permission to view this lifegroup');
                $this->redirect('/lifegroup');
                return;
            }
        } elseif ($userRole === ROLE_MENTOR) {
            // Mentors can only view their own lifegroups
            if ($lifegroup['mentor_id'] != $userId) {
                setFlash('error', 'You do not have permission to view this lifegroup');
                $this->redirect('/lifegroup');
                return;
            }
        } else {
            setFlash('error', 'You do not have permission to view this lifegroup');
            $this->redirect('/lifegroup');
            return;
        }
        
        $members = $this->lifegroupModel->getLifegroupMembers($id);
        
        $this->view('lifegroup/view', [
            'title' => 'View Lifegroup',
            'lifegroup' => $lifegroup,
            'members' => $members
        ]);
    }
    
    public function getMentorsByChurch(string $churchId): void
    {
        $this->requireAuth();
        
        $mentors = $this->userModel->getMentorsByChurch((int) $churchId);
        
        header('Content-Type: application/json');
        echo json_encode($mentors);
        exit;
    }
} 