<?php

namespace App\Models;

use App\Core\Model;

class UserModel extends Model
{
    protected string $table = 'users';
    
    public function authenticate(string $email, string $password): array|false
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = ? AND status = 'active'";
        $user = $this->db->fetch($sql, [$email]);
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }
    
    public function findByEmail(string $email): array|false
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = ?";
        return $this->db->fetch($sql, [$email]);
    }
    
    public function find(int $id): array|false
    {
        return $this->findById($id);
    }
    
    public function create(array $data): int
    {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        return parent::create($data);
    }
    
    public function update(int $id, array $data): bool
    {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }
        $data['updated_at'] = date('Y-m-d H:i:s');
        return parent::update($id, $data);
    }
    
    public function getUsersByRole(string $role): array
    {
        return $this->findAll(['role' => $role], 'name ASC');
    }
    
    public function getPastorsWithChurches(): array
    {
        $sql = "SELECT u.*, c.name as church_name 
                FROM {$this->table} u 
                LEFT JOIN churches c ON u.church_id = c.id 
                WHERE u.role = 'pastor' 
                ORDER BY u.name ASC";
        return $this->db->fetchAll($sql);
    }
    
    public function getCoachesWithChurchesAndPastors(): array
    {
        $sql = "SELECT u.*, c.name as church_name, p.name as pastor_name 
                FROM {$this->table} u 
                LEFT JOIN churches c ON u.church_id = c.id 
                LEFT JOIN users p ON c.pastor_id = p.id 
                WHERE u.role = 'coach' 
                ORDER BY u.name ASC";
        return $this->db->fetchAll($sql);
    }
    
    public function getMentorsWithChurchesAndPastors(): array
    {
        $sql = "SELECT u.*, c.name as church_name, p.name as pastor_name, co.name as coach_name 
                FROM {$this->table} u 
                LEFT JOIN churches c ON u.church_id = c.id 
                LEFT JOIN users p ON c.pastor_id = p.id 
                LEFT JOIN hierarchy h ON u.id = h.user_id 
                LEFT JOIN users co ON h.parent_id = co.id AND co.role = 'coach'
                WHERE u.role = 'mentor' 
                ORDER BY u.name ASC";
        return $this->db->fetchAll($sql);
    }
    
    public function getCoachesForSelection(): array
    {
        $sql = "SELECT u.id, u.name, c.name as church_name 
                FROM {$this->table} u 
                LEFT JOIN churches c ON u.church_id = c.id 
                WHERE u.role = 'coach' AND u.status = 'active' 
                ORDER BY u.name ASC";
        return $this->db->fetchAll($sql);
    }
    
    public function getCoachesByChurch(int $churchId): array
    {
        $sql = "SELECT u.id, u.name, c.name as church_name 
                FROM {$this->table} u 
                LEFT JOIN churches c ON u.church_id = c.id 
                WHERE u.role = 'coach' AND u.status = 'active' AND u.church_id = ? 
                ORDER BY u.name ASC";
        return $this->db->fetchAll($sql, [$churchId]);
    }
    
    public function getMentorsByChurch(int $churchId): array
    {
        $sql = "SELECT u.id, u.name, c.name as church_name 
                FROM {$this->table} u 
                LEFT JOIN churches c ON u.church_id = c.id 
                WHERE u.role = 'mentor' AND u.status = 'active' AND u.church_id = ? 
                ORDER BY u.name ASC";
        return $this->db->fetchAll($sql, [$churchId]);
    }
    
    public function getMentorsByCoach(int $coachId): array
    {
        $sql = "SELECT u.id, u.name, c.name as church_name 
                FROM {$this->table} u 
                LEFT JOIN churches c ON u.church_id = c.id 
                LEFT JOIN hierarchy h ON u.id = h.user_id 
                WHERE u.role = 'mentor' AND u.status = 'active' AND h.parent_id = ? 
                ORDER BY u.name ASC";
        return $this->db->fetchAll($sql, [$coachId]);
    }
    
    public function getMentorsForSelection(): array
    {
        $sql = "SELECT u.id, u.name, c.name as church_name 
                FROM {$this->table} u 
                LEFT JOIN churches c ON u.church_id = c.id 
                WHERE u.role = 'mentor' AND u.status = 'active' 
                ORDER BY u.name ASC";
        return $this->db->fetchAll($sql);
    }
    
    public function createHierarchyRelationship(int $userId, int $parentId): bool
    {
        $sql = "INSERT INTO hierarchy (user_id, parent_id) VALUES (?, ?)";
        return $this->db->query($sql, [$userId, $parentId])->rowCount() > 0;
    }
    
    public function updateHierarchyRelationship(int $userId, int $parentId): bool
    {
        // First delete existing relationship
        $deleteSql = "DELETE FROM hierarchy WHERE user_id = ?";
        $this->db->query($deleteSql, [$userId]);
        
        // Then create new relationship
        if ($parentId) {
            return $this->createHierarchyRelationship($userId, $parentId);
        }
        return true;
    }
    
    public function getHierarchyParent(int $userId): array|false
    {
        $sql = "SELECT u.* FROM {$this->table} u 
                INNER JOIN hierarchy h ON u.id = h.parent_id 
                WHERE h.user_id = ?";
        return $this->db->fetch($sql, [$userId]);
    }
    
    public function getHierarchyUsers(int $userId, string $role): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE role = ? AND id IN (
            SELECT user_id FROM hierarchy WHERE parent_id = ?
        )";
        return $this->db->fetchAll($sql, [$role, $userId]);
    }
    
    public function getUsersByChurch(int $churchId): array
    {
        return $this->findAll(['church_id' => $churchId], 'name ASC');
    }
} 