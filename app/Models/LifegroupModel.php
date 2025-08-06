<?php

namespace App\Models;

use App\Core\Model;

class LifegroupModel extends Model
{
    protected string $table = 'lifegroups';
    
    public function getAllLifegroups(): array
    {
        $sql = "SELECT l.*, c.name as church_name, u.name as mentor_name,
                       (SELECT COUNT(*) FROM lifegroup_members WHERE lifegroup_id = l.id AND status = 'active') as member_count
                FROM {$this->table} l 
                LEFT JOIN churches c ON l.church_id = c.id 
                LEFT JOIN users u ON l.mentor_id = u.id 
                ORDER BY l.name ASC";
        return $this->db->fetchAll($sql);
    }
    
    public function getLifegroupsByChurch(int $churchId): array
    {
        $sql = "SELECT l.*, c.name as church_name, u.name as mentor_name,
                       (SELECT COUNT(*) FROM lifegroup_members WHERE lifegroup_id = l.id AND status = 'active') as member_count
                FROM {$this->table} l 
                LEFT JOIN churches c ON l.church_id = c.id 
                LEFT JOIN users u ON l.mentor_id = u.id 
                WHERE l.church_id = ? 
                ORDER BY l.name ASC";
        return $this->db->fetchAll($sql, [$churchId]);
    }
    
    public function getLifegroupsByMentor(int $mentorId): array
    {
        $sql = "SELECT l.*, c.name as church_name, u.name as mentor_name,
                       (SELECT COUNT(*) FROM lifegroup_members WHERE lifegroup_id = l.id AND status = 'active') as member_count
                FROM {$this->table} l 
                LEFT JOIN churches c ON l.church_id = c.id 
                LEFT JOIN users u ON l.mentor_id = u.id 
                WHERE l.mentor_id = ? 
                ORDER BY l.name ASC";
        return $this->db->fetchAll($sql, [$mentorId]);
    }
    
    public function getLifegroupWithDetails(int $id): array|false
    {
        $sql = "SELECT l.*, c.name as church_name, u.name as mentor_name,
                       (SELECT COUNT(*) FROM lifegroup_members WHERE lifegroup_id = l.id AND status = 'active') as member_count
                FROM {$this->table} l 
                LEFT JOIN churches c ON l.church_id = c.id 
                LEFT JOIN users u ON l.mentor_id = u.id 
                WHERE l.id = ?";
        return $this->db->fetch($sql, [$id]);
    }
    
    public function getLifegroupMembers(int $lifegroupId): array
    {
        $sql = "SELECT lm.*, u.name, u.email, u.phone, u.status as user_status
                FROM lifegroup_members lm 
                LEFT JOIN users u ON lm.user_id = u.id 
                WHERE lm.lifegroup_id = ? AND lm.status = 'active'
                ORDER BY u.name ASC";
        return $this->db->fetchAll($sql, [$lifegroupId]);
    }
    
    public function addMemberToLifegroup(int $lifegroupId, int $userId): bool
    {
        // Check if user is already a member
        $existing = $this->db->fetch(
            "SELECT id FROM lifegroup_members WHERE lifegroup_id = ? AND user_id = ?",
            [$lifegroupId, $userId]
        );
        
        if ($existing) {
            // Update existing membership to active
            return $this->db->query(
                "UPDATE lifegroup_members SET status = 'active', updated_at = NOW() WHERE lifegroup_id = ? AND user_id = ?",
                [$lifegroupId, $userId]
            )->rowCount() > 0;
        }
        
        // Add new membership
        return $this->db->query(
            "INSERT INTO lifegroup_members (lifegroup_id, user_id, joined_date, status) VALUES (?, ?, CURDATE(), 'active')",
            [$lifegroupId, $userId]
        )->rowCount() > 0;
    }
    
    public function removeMemberFromLifegroup(int $lifegroupId, int $userId): bool
    {
        return $this->db->query(
            "UPDATE lifegroup_members SET status = 'left', updated_at = NOW() WHERE lifegroup_id = ? AND user_id = ?",
            [$lifegroupId, $userId]
        )->rowCount() > 0;
    }
    
    public function getMemberLifegroup(int $userId): array|false
    {
        $sql = "SELECT lm.*, l.name as lifegroup_name 
                FROM lifegroup_members lm 
                LEFT JOIN lifegroups l ON lm.lifegroup_id = l.id 
                WHERE lm.user_id = ? AND lm.status = 'active'";
        return $this->db->fetch($sql, [$userId]);
    }
    
    public function removeMemberFromAllLifegroups(int $userId): bool
    {
        return $this->db->query(
            "UPDATE lifegroup_members SET status = 'left', updated_at = NOW() WHERE user_id = ? AND status = 'active'",
            [$userId]
        )->rowCount() > 0;
    }
    
    public function getAvailableMentors(int $churchId): array
    {
        $sql = "SELECT id, name FROM users WHERE role = 'mentor' AND church_id = ? AND status = 'active' ORDER BY name ASC";
        return $this->db->fetchAll($sql, [$churchId]);
    }
    
    public function getAvailableMembers(int $churchId): array
    {
        $sql = "SELECT id, name, email FROM users WHERE role = 'member' AND church_id = ? AND status = 'active' ORDER BY name ASC";
        return $this->db->fetchAll($sql, [$churchId]);
    }
    
    public function updateLifegroupStatus(int $id, string $status): bool
    {
        return $this->update($id, ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
    }
} 