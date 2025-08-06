<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ChurchModel;
use App\Models\MemberModel;
use App\Models\UserModel;

class DashboardController extends Controller
{
    private ChurchModel $churchModel;
    private MemberModel $memberModel;
    private UserModel $userModel;
    
    public function __construct()
    {
        $this->churchModel = new ChurchModel();
        $this->memberModel = new MemberModel();
        $this->userModel = new UserModel();
    }
    
    public function index(): void
    {
        $this->requireAuth();
        
        $userRole = $this->getUserRole();
        $churchId = $_SESSION['church_id'] ?? null;
        
        $data = [
            'user' => [
                'name' => $_SESSION['user_name'],
                'role' => $userRole,
                'church_id' => $churchId
            ]
        ];
        
        // Get statistics based on user role
        switch ($userRole) {
            case ROLE_SUPER_ADMIN:
                $data['churches'] = $this->churchModel->getAllChurches();
                $data['stats'] = $this->memberModel->getMemberStats();
                break;
                
            case ROLE_PASTOR:
                // Get church_id from session or user record
                if (!$churchId) {
                    $user = $this->userModel->findById($_SESSION['user_id']);
                    $churchId = $user['church_id'] ?? null;
                    if ($churchId) {
                        $_SESSION['church_id'] = $churchId;
                    }
                }
                
                if ($churchId) {
                    $data['church'] = $this->churchModel->getChurchWithDetails((int)$churchId);
                    $data['stats'] = $this->memberModel->getMemberStats((int)$churchId);
                } else {
                    $data['church'] = null;
                    $data['stats'] = [];
                }
                $data['members'] = $this->memberModel->getMembersByPastor($_SESSION['user_id']);
                break;
                
            case ROLE_COACH:
                $data['members'] = $this->memberModel->getMembersByCoach($_SESSION['user_id']);
                $data['stats'] = $this->getCoachStats($_SESSION['user_id']);
                break;
                
            case ROLE_MENTOR:
                $data['members'] = $this->memberModel->getMembersByMentor($_SESSION['user_id']);
                $data['stats'] = $this->getMentorStats($_SESSION['user_id']);
                break;
                
            case ROLE_MEMBER:
                $data['profile'] = $this->memberModel->getMemberWithHierarchy($_SESSION['user_id']);
                break;
        }
        
        $this->view('dashboard/index', $data);
    }
    
    private function getCoachStats(int $coachId): array
    {
        $members = $this->memberModel->getMembersByCoach($coachId);
        $total = count($members);
        $active = count(array_filter($members, fn($m) => $m['status'] === 'active'));
        
        return [
            'total_members' => $total,
            'active_members' => $active,
            'inactive_members' => $total - $active
        ];
    }
    
    private function getMentorStats(int $mentorId): array
    {
        $members = $this->memberModel->getMembersByMentor($mentorId);
        $total = count($members);
        $active = count(array_filter($members, fn($m) => $m['status'] === 'active'));
        
        return [
            'total_members' => $total,
            'active_members' => $active,
            'inactive_members' => $total - $active
        ];
    }
} 