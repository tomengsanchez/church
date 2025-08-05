<?php

namespace App\Models;

use App\Core\Model;

class ChurchModel extends Model
{
    protected string $table = 'churches';
    
    public function getChurchWithDetails(int $id): array|false
    {
        $sql = "SELECT c.*, u.name as pastor_name, u.email as pastor_email 
                FROM {$this->table} c 
                LEFT JOIN users u ON c.pastor_id = u.id 
                WHERE c.id = ?";
        return $this->db->fetch($sql, [$id]);
    }
    
    public function getAllChurches(): array
    {
        $sql = "SELECT c.*, u.name as pastor_name, 
                (SELECT COUNT(*) FROM users WHERE church_id = c.id AND role = 'coach') as coach_count,
                (SELECT COUNT(*) FROM users WHERE church_id = c.id AND role = 'mentor') as mentor_count,
                (SELECT COUNT(*) FROM users WHERE church_id = c.id AND role = 'member') as member_count
                FROM {$this->table} c 
                LEFT JOIN users u ON c.pastor_id = u.id 
                ORDER BY c.name ASC";
        return $this->db->fetchAll($sql);
    }
    
    public function getChurchStats(int $churchId): array|false
    {
        $sql = "SELECT 
                (SELECT COUNT(*) FROM users WHERE church_id = ? AND role = 'coach') as coach_count,
                (SELECT COUNT(*) FROM users WHERE church_id = ? AND role = 'mentor') as mentor_count,
                (SELECT COUNT(*) FROM users WHERE church_id = ? AND role = 'member') as member_count,
                (SELECT COUNT(*) FROM users WHERE church_id = ? AND role = 'member' AND status = 'active') as active_members,
                (SELECT COUNT(*) FROM users WHERE church_id = ? AND role = 'member' AND status = 'inactive') as inactive_members";
        return $this->db->fetch($sql, [$churchId, $churchId, $churchId, $churchId, $churchId]);
    }
    
    public function getChurchesByPastor(int $pastorId): array
    {
        return $this->findAll(['pastor_id' => $pastorId], 'name ASC');
    }
} 