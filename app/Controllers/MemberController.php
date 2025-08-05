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
        $this->requireRole(ROLE_MENTOR);
        
        $churchId = $_SESSION['church_id'];
        $userRole = $this->getUserRole();
        
        $members = [];
        switch ($userRole) {
            case ROLE_PASTOR:
                $members = $this->memberModel->getMembersByPastor($_SESSION['user_id']);
                break;
            case ROLE_COACH:
                $members = $this->memberModel->getMembersByCoach($_SESSION['user_id']);
                break;
            case ROLE_MENTOR:
                $members = $this->memberModel->getMembersByMentor($_SESSION['user_id']);
                break;
        }
        
        $this->view('member/index', [
            'members' => $members,
            'churches' => $this->churchModel->getAllChurches(),
            'stats' => $this->memberModel->getMemberStats($churchId)
        ]);
    }
    
    public function create(): void
    {
        $this->requireRole(ROLE_MENTOR);
        
        $this->view('member/create');
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
            'role' => 'member',
            'church_id' => $_SESSION['church_id'],
            'status' => $_POST['status'] ?? 'active'
        ];
        
        // Validation
        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            flash('Name, email and password are required', 'error');
            $this->view('member/create', ['data' => $data]);
            return;
        }
        
        if ($this->userModel->findByEmail($data['email'])) {
            flash('Email already exists', 'error');
            $this->view('member/create', ['data' => $data]);
            return;
        }
        
        $userId = $this->userModel->createUser($data);
        
        if ($userId) {
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
        
        $this->view('member/edit', ['member' => $member]);
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
            'status' => $_POST['status'] ?? 'active'
        ];
        
        if (!empty($_POST['password'])) {
            $data['password'] = $_POST['password'];
        }
        
        if ($this->userModel->updateUser($memberId, $data)) {
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
} 