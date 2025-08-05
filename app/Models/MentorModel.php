<?php

namespace App\Models;

use App\Core\Model;

class MentorModel extends Model
{
    protected string $table = 'users';
    
    public function getMentorsByCoach(int $coachId): array
    {
        $sql = "SELECT u.*, c.name as church_name, p.name as pastor_name, co.name as coach_name
                FROM users u 
                LEFT JOIN churches c ON u.church_id = c.id 
                LEFT JOIN users p ON u.pastor_id = p.id 
                LEFT JOIN users co ON u.coach_id = co.id 
                WHERE u.role = ? AND u.coach_id = ? AND u.status = 'active'
                ORDER BY u.name";
        
        return $this->query($sql, [ROLE_MENTOR, $coachId]);
    }
    
    public function getAllMentors(): array
    {
        $sql = "SELECT u.*, c.name as church_name, p.name as pastor_name, co.name as coach_name
                FROM users u 
                LEFT JOIN churches c ON u.church_id = c.id 
                LEFT JOIN users p ON u.pastor_id = p.id 
                LEFT JOIN users co ON u.coach_id = co.id 
                WHERE u.role = ? 
                ORDER BY u.name";
        
        return $this->query($sql, [ROLE_MENTOR]);
    }
    
    public function getMentorWithDetails(int $mentorId): ?array
    {
        $sql = "SELECT u.*, c.name as church_name, c.address as church_address, p.name as pastor_name, co.name as coach_name
                FROM users u 
                LEFT JOIN churches c ON u.church_id = c.id 
                LEFT JOIN users p ON u.pastor_id = p.id 
                LEFT JOIN users co ON u.coach_id = co.id 
                WHERE u.id = ? AND u.role = ?";
        
        $result = $this->query($sql, [$mentorId, ROLE_MENTOR]);
        return $result ? $result[0] : null;
    }
    
    public function getMentorStats(int $mentorId): array
    {
        // Get members under this mentor
        $membersSql = "SELECT COUNT(*) as total_members FROM users WHERE mentor_id = ? AND role = ? AND status = 'active'";
        $membersResult = $this->query($membersSql, [$mentorId, ROLE_MEMBER]);
        $totalMembers = $membersResult[0]['total_members'] ?? 0;
        
        // Get active members
        $activeMembersSql = "SELECT COUNT(*) as active_members FROM users WHERE mentor_id = ? AND role = ? AND status = 'active'";
        $activeResult = $this->query($activeMembersSql, [$mentorId, ROLE_MEMBER]);
        $activeMembers = $activeResult[0]['active_members'] ?? 0;
        
        return [
            'total_members' => $totalMembers,
            'active_members' => $activeMembers,
            'inactive_members' => $totalMembers - $activeMembers
        ];
    }
    
    public function createMentor(array $data): int
    {
        $data['role'] = ROLE_MENTOR;
        $data['status'] = 'active';
        $data['created_at'] = date('Y-m-d H:i:s');
        
        return $this->insert($data);
    }
    
    public function updateMentor(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($id, $data);
    }
    
    public function deleteMentor(int $id): bool
    {
        // Check if mentor has any members
        $membersSql = "SELECT COUNT(*) as count FROM users WHERE mentor_id = ? AND status = 'active'";
        $result = $this->query($membersSql, [$id]);
        $hasMembers = ($result[0]['count'] ?? 0) > 0;
        
        if ($hasMembers) {
            return false; // Cannot delete mentor with active members
        }
        
        return $this->delete($id);
    }
} 