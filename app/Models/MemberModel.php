<?php

namespace App\Models;

use App\Core\Model;

class MemberModel extends Model
{
    protected string $table = 'users';
    
    public function getMembersByChurch(int $churchId, ?string $status = null): array
    {
        $conditions = ['church_id' => $churchId, 'role' => 'member'];
        if ($status) {
            $conditions['status'] = $status;
        }
        return $this->findAll($conditions, 'name ASC');
    }
    
    public function getMembersByMentor(int $mentorId): array
    {
        $sql = "SELECT u.* FROM {$this->table} u 
                INNER JOIN hierarchy h ON u.id = h.user_id 
                WHERE h.parent_id = ? AND u.role = 'member' 
                ORDER BY u.name ASC";
        return $this->db->fetchAll($sql, [$mentorId]);
    }
    
    public function getMembersByCoach(int $coachId): array
    {
        $sql = "SELECT u.* FROM {$this->table} u 
                INNER JOIN hierarchy h1 ON u.id = h1.user_id
                INNER JOIN users m ON h1.parent_id = m.id AND m.role = 'mentor'
                INNER JOIN hierarchy h2 ON m.id = h2.user_id
                WHERE h2.parent_id = ? AND u.role = 'member' 
                ORDER BY u.name ASC";
        return $this->db->fetchAll($sql, [$coachId]);
    }
    
    public function getMembersByPastor(int $pastorId): array
    {
        $sql = "SELECT u.* FROM {$this->table} u 
                WHERE u.church_id = (SELECT church_id FROM users WHERE id = ?) 
                AND u.role = 'member' 
                ORDER BY u.name ASC";
        return $this->db->fetchAll($sql, [$pastorId]);
    }
    
    public function updateMemberStatus(int $id, string $status): bool
    {
        return $this->update($id, ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
    }
    
    public function getMemberWithHierarchy(int $id): array|false
    {
        $sql = "SELECT u.*, 
                ch.name as church_name,
                p.name as pastor_name,
                c.name as coach_name,
                m.name as mentor_name
                FROM {$this->table} u 
                LEFT JOIN churches ch ON u.church_id = ch.id
                LEFT JOIN users p ON ch.pastor_id = p.id
                LEFT JOIN hierarchy h1 ON u.id = h1.user_id
                LEFT JOIN users m ON h1.parent_id = m.id AND m.role = 'mentor'
                LEFT JOIN hierarchy h2 ON m.id = h2.user_id
                LEFT JOIN users c ON h2.parent_id = c.id AND c.role = 'coach'
                WHERE u.id = ?";
        return $this->db->fetch($sql, [$id]);
    }
    
    public function getAllMembersWithHierarchy(): array
    {
        $sql = "SELECT u.*, 
                ch.name as church_name,
                p.name as pastor_name,
                c.name as coach_name,
                m.name as mentor_name
                FROM {$this->table} u 
                LEFT JOIN churches ch ON u.church_id = ch.id
                LEFT JOIN users p ON ch.pastor_id = p.id
                LEFT JOIN hierarchy h1 ON u.id = h1.user_id
                LEFT JOIN users m ON h1.parent_id = m.id AND m.role = 'mentor'
                LEFT JOIN hierarchy h2 ON m.id = h2.user_id
                LEFT JOIN users c ON h2.parent_id = c.id AND c.role = 'coach'
                WHERE u.role = 'member'
                ORDER BY u.name ASC";
        return $this->db->fetchAll($sql);
    }
    
    public function getMemberStats(?int $churchId = null): array|false
    {
        $sql = "SELECT 
                COUNT(*) as total_members,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_members,
                SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_members,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_members,
                SUM(CASE WHEN status = 'suspended' THEN 1 ELSE 0 END) as suspended_members
                FROM {$this->table} WHERE role = 'member'";
        
        if ($churchId) {
            $sql .= " AND church_id = ?";
            return $this->db->fetch($sql, [$churchId]);
        }
        
        return $this->db->fetch($sql);
    }
    
    public function searchMembers(string $search, ?int $churchId = null): array
    {
        $sql = "SELECT u.*, 
                ch.name as church_name,
                p.name as pastor_name,
                c.name as coach_name,
                m.name as mentor_name
                FROM {$this->table} u 
                LEFT JOIN churches ch ON u.church_id = ch.id
                LEFT JOIN users p ON ch.pastor_id = p.id
                LEFT JOIN hierarchy h1 ON u.id = h1.user_id
                LEFT JOIN users m ON h1.parent_id = m.id AND m.role = 'mentor'
                LEFT JOIN hierarchy h2 ON m.id = h2.user_id
                LEFT JOIN users c ON h2.parent_id = c.id AND c.role = 'coach'
                WHERE u.role = 'member' AND (u.name LIKE ? OR u.email LIKE ?)";
        $params = ["%$search%", "%$search%"];
        
        if ($churchId) {
            $sql .= " AND u.church_id = ?";
            $params[] = $churchId;
        }
        
        $sql .= " ORDER BY u.name ASC";
        return $this->db->fetchAll($sql, $params);
    }
} 