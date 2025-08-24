<?php

namespace App\Models;

use App\Core\Model;

class CoachModel extends Model
{
    protected string $table = 'users';
    
    public function getCoachesByPastor(int $pastorId): array
    {
        $sql = "SELECT u.*, c.name as church_name, p.name as pastor_name
                FROM users u 
                LEFT JOIN churches c ON u.church_id = c.id 
                LEFT JOIN users p ON c.pastor_id = p.id 
                WHERE u.role = ? AND c.pastor_id = ? AND u.status = 'active'
                ORDER BY u.name";
        
        return $this->db->fetchAll($sql, [ROLE_COACH, $pastorId]);
    }
    
    public function getAllCoaches(): array
    {
        $sql = "SELECT u.*, c.name as church_name, p.name as pastor_name
                FROM users u 
                LEFT JOIN churches c ON u.church_id = c.id 
                LEFT JOIN users p ON c.pastor_id = p.id 
                WHERE u.role = ? 
                ORDER BY u.name";
        
        return $this->db->fetchAll($sql, [ROLE_COACH]);
    }
    
    public function getCoachWithDetails(int $coachId): ?array
    {
        $sql = "SELECT u.*, c.name as church_name, c.address as church_address, p.name as pastor_name
                FROM users u 
                LEFT JOIN churches c ON u.church_id = c.id 
                LEFT JOIN users p ON c.pastor_id = p.id 
                WHERE u.id = ? AND u.role = ?";
        
        $result = $this->db->fetch($sql, [$coachId, ROLE_COACH]);
        return $result ? $result : null;
    }
    
    public function getCoachStats(int $coachId): array
    {
        // Get mentors under this coach
        $mentorsSql = "SELECT COUNT(*) as total_mentors FROM users WHERE coach_id = ? AND role = ? AND status = 'active'";
        $mentorsResult = $this->db->fetch($mentorsSql, [$coachId, ROLE_MENTOR]);
        $totalMentors = $mentorsResult['total_mentors'] ?? 0;
        
        // Get members under this coach
        $membersSql = "SELECT COUNT(*) as total_members FROM users WHERE coach_id = ? AND role = ? AND status = 'active'";
        $membersResult = $this->db->fetch($membersSql, [$coachId, ROLE_MEMBER]);
        $totalMembers = $membersResult['total_members'] ?? 0;
        
        return [
            'total_mentors' => $totalMentors,
            'total_members' => $totalMembers,
            'total_people' => $totalMentors + $totalMembers
        ];
    }
    
    public function createCoach(array $data): int
    {
        $data['role'] = ROLE_COACH;
        $data['status'] = 'active';
        $data['created_at'] = date('Y-m-d H:i:s');
        
        return $this->insert($data);
    }
    
    public function updateCoach(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($id, $data);
    }
    
    public function deleteCoach(int $id): bool
    {
        // Check if coach has any subordinates
        $subordinatesSql = "SELECT COUNT(*) as count FROM users WHERE coach_id = ? AND status = 'active'";
        $result = $this->db->fetch($subordinatesSql, [$id]);
        $hasSubordinates = ($result['count'] ?? 0) > 0;
        
        if ($hasSubordinates) {
            return false; // Cannot delete coach with active subordinates
        }
        
        return $this->delete($id);
    }
} 