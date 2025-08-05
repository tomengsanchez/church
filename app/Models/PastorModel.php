<?php

namespace App\Models;

use App\Core\Model;

class PastorModel extends Model
{
    protected string $table = 'users';
    
    public function getPastorsByChurch(int $churchId): array
    {
        $sql = "SELECT u.*, c.name as church_name 
                FROM users u 
                LEFT JOIN churches c ON u.church_id = c.id 
                WHERE u.role = ? AND u.church_id = ? AND u.status = 'active'
                ORDER BY u.name";
        
        return $this->query($sql, [ROLE_PASTOR, $churchId]);
    }
    
    public function getAllPastors(): array
    {
        $sql = "SELECT u.*, c.name as church_name 
                FROM users u 
                LEFT JOIN churches c ON u.church_id = c.id 
                WHERE u.role = ? 
                ORDER BY u.name";
        
        return $this->query($sql, [ROLE_PASTOR]);
    }
    
    public function getPastorWithDetails(int $pastorId): ?array
    {
        $sql = "SELECT u.*, c.name as church_name, c.address as church_address
                FROM users u 
                LEFT JOIN churches c ON u.church_id = c.id 
                WHERE u.id = ? AND u.role = ?";
        
        $result = $this->query($sql, [$pastorId, ROLE_PASTOR]);
        return $result ? $result[0] : null;
    }
    
    public function getPastorStats(int $pastorId): array
    {
        // Get coaches under this pastor
        $coachesSql = "SELECT COUNT(*) as total_coaches FROM users WHERE pastor_id = ? AND role = ? AND status = 'active'";
        $coachesResult = $this->query($coachesSql, [$pastorId, ROLE_COACH]);
        $totalCoaches = $coachesResult[0]['total_coaches'] ?? 0;
        
        // Get mentors under this pastor
        $mentorsSql = "SELECT COUNT(*) as total_mentors FROM users WHERE pastor_id = ? AND role = ? AND status = 'active'";
        $mentorsResult = $this->query($mentorsSql, [$pastorId, ROLE_MENTOR]);
        $totalMentors = $mentorsResult[0]['total_mentors'] ?? 0;
        
        // Get members under this pastor
        $membersSql = "SELECT COUNT(*) as total_members FROM users WHERE pastor_id = ? AND role = ? AND status = 'active'";
        $membersResult = $this->query($membersSql, [$pastorId, ROLE_MEMBER]);
        $totalMembers = $membersResult[0]['total_members'] ?? 0;
        
        return [
            'total_coaches' => $totalCoaches,
            'total_mentors' => $totalMentors,
            'total_members' => $totalMembers,
            'total_people' => $totalCoaches + $totalMentors + $totalMembers
        ];
    }
    
    public function createPastor(array $data): int
    {
        $data['role'] = ROLE_PASTOR;
        $data['status'] = 'active';
        $data['created_at'] = date('Y-m-d H:i:s');
        
        return $this->insert($data);
    }
    
    public function updatePastor(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($id, $data);
    }
    
    public function deletePastor(int $id): bool
    {
        // Check if pastor has any subordinates
        $subordinatesSql = "SELECT COUNT(*) as count FROM users WHERE pastor_id = ? AND status = 'active'";
        $result = $this->query($subordinatesSql, [$id]);
        $hasSubordinates = ($result[0]['count'] ?? 0) > 0;
        
        if ($hasSubordinates) {
            return false; // Cannot delete pastor with active subordinates
        }
        
        return $this->delete($id);
    }
} 