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
        // Handle optional password
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            // If no password provided, generate a random one or set a default
            $data['password'] = password_hash('changeme123', PASSWORD_DEFAULT);
        }
        
        // Handle optional email - if empty, generate a unique placeholder
        if (empty($data['email'])) {
            $data['email'] = $this->generateUniqueEmail();
        }
        
        $data['created_at'] = date('Y-m-d H:i:s');
        return parent::create($data);
    }
    
    public function update(int $id, array $data): bool
    {
        // Handle optional password
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }
        
        // Handle optional email - if empty, generate a unique placeholder
        if (isset($data['email']) && empty($data['email'])) {
            $data['email'] = $this->generateUniqueEmail();
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        return parent::update($id, $data);
    }
    
    /**
     * Generate a unique email placeholder for users without email
     */
    private function generateUniqueEmail(): string
    {
        $timestamp = time();
        $random = mt_rand(1000, 9999);
        $email = "no-email-{$timestamp}-{$random}@placeholder.local";
        
        // Ensure it's unique by checking if it exists
        while ($this->findByEmail($email)) {
            $timestamp = time();
            $random = mt_rand(1000, 9999);
            $email = "no-email-{$timestamp}-{$random}@placeholder.local";
        }
        
        return $email;
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
    
    public function getCoachesWithChurchesAndPastorsByChurch(int $churchId): array
    {
        $sql = "SELECT u.*, c.name as church_name, p.name as pastor_name 
                FROM {$this->table} u 
                LEFT JOIN churches c ON u.church_id = c.id 
                LEFT JOIN users p ON c.pastor_id = p.id 
                WHERE u.role = 'coach' AND u.church_id = ? 
                ORDER BY u.name ASC";
        return $this->db->fetchAll($sql, [$churchId]);
    }
    
    public function getMentorsWithChurchesAndPastors(): array
    {
        $sql = "SELECT u.*, c.name as church_name, p.name as pastor_name, co.name as coach_name, co.id as coach_id 
                FROM {$this->table} u 
                LEFT JOIN churches c ON u.church_id = c.id 
                LEFT JOIN users p ON c.pastor_id = p.id 
                LEFT JOIN hierarchy h ON u.id = h.user_id 
                LEFT JOIN users co ON h.parent_id = co.id AND co.role = 'coach'
                WHERE u.role = 'mentor' 
                ORDER BY u.name ASC";
        return $this->db->fetchAll($sql);
    }
    
    public function getMentorsWithChurchesAndPastorsByChurch(int $churchId): array
    {
        $sql = "SELECT u.*, c.name as church_name, p.name as pastor_name, co.name as coach_name, co.id as coach_id 
                FROM {$this->table} u 
                LEFT JOIN churches c ON u.church_id = c.id 
                LEFT JOIN users p ON c.pastor_id = p.id 
                LEFT JOIN hierarchy h ON u.id = h.user_id 
                LEFT JOIN users co ON h.parent_id = co.id AND co.role = 'coach'
                WHERE u.role = 'mentor' AND u.church_id = ? 
                ORDER BY u.name ASC";
        return $this->db->fetchAll($sql, [$churchId]);
    }
    
    public function getMentorsWithChurchesAndPastorsByCoach(int $coachId): array
    {
        $sql = "SELECT u.*, c.name as church_name, p.name as pastor_name, co.name as coach_name 
                FROM {$this->table} u 
                LEFT JOIN churches c ON u.church_id = c.id 
                LEFT JOIN users p ON c.pastor_id = p.id 
                LEFT JOIN hierarchy h ON u.id = h.user_id 
                LEFT JOIN users co ON h.parent_id = co.id AND co.role = 'coach'
                WHERE u.role = 'mentor' AND h.parent_id = ? 
                ORDER BY u.name ASC";
        return $this->db->fetchAll($sql, [$coachId]);
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
    
    public function updateHierarchyRelationship(int $userId, ?int $parentId): bool
    {
        // First delete existing relationship
        $deleteSql = "DELETE FROM hierarchy WHERE user_id = ?";
        $this->db->query($deleteSql, [$userId]);
        
        // Then create new relationship if parentId is provided
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
    
    /**
     * Remove any direct relationships between a member and coaches
     * Members should only be assigned to mentors, not directly to coaches
     */
    public function removeDirectCoachRelationships(int $memberId): bool
    {
        $sql = "DELETE h FROM hierarchy h 
                INNER JOIN users u ON h.parent_id = u.id 
                WHERE h.user_id = ? AND u.role = 'coach'";
        return $this->db->query($sql, [$memberId])->rowCount() >= 0;
    }
    
    public function getUsersByChurch(int $churchId): array
    {
        return $this->findAll(['church_id' => $churchId], 'name ASC');
    }
    
    public function getUsersByRoleAndChurch(string $role, int $churchId): array
    {
        return $this->findAll(['role' => $role, 'church_id' => $churchId], 'name ASC');
    }
    
    // Friend-specific methods
    public function getFriendsByStatus(string $status): array
    {
        $sql = "SELECT u.*, c.name as church_name 
                FROM {$this->table} u 
                LEFT JOIN churches c ON u.church_id = c.id 
                WHERE u.role = 'member' AND u.status = ? 
                ORDER BY u.created_at DESC";
        return $this->db->fetchAll($sql, [$status]);
    }
    
    public function getFriendsByPastor(int $pastorId): array
    {
        $sql = "SELECT u.*, c.name as church_name 
                FROM {$this->table} u 
                LEFT JOIN churches c ON u.church_id = c.id 
                WHERE u.role = 'member' AND u.status = 'pending' AND c.pastor_id = ? 
                ORDER BY u.created_at DESC";
        return $this->db->fetchAll($sql, [$pastorId]);
    }
    
    public function getFriendsByCoach(int $coachId): array
    {
        $sql = "SELECT u.*, c.name as church_name 
                FROM {$this->table} u 
                LEFT JOIN churches c ON u.church_id = c.id 
                LEFT JOIN hierarchy h ON u.id = h.user_id 
                LEFT JOIN users mentor ON h.parent_id = mentor.id 
                LEFT JOIN hierarchy h2 ON mentor.id = h2.user_id 
                WHERE u.role = 'member' AND u.status = 'pending' AND h2.parent_id = ? 
                ORDER BY u.created_at DESC";
        return $this->db->fetchAll($sql, [$coachId]);
    }
    
    public function getFriendsByMentor(int $mentorId): array
    {
        $sql = "SELECT u.*, c.name as church_name 
                FROM {$this->table} u 
                LEFT JOIN churches c ON u.church_id = c.id 
                LEFT JOIN hierarchy h ON u.id = h.user_id 
                WHERE u.role = 'member' AND u.status = 'pending' AND h.parent_id = ? 
                ORDER BY u.created_at DESC";
        return $this->db->fetchAll($sql, [$mentorId]);
    }
    
    public function countFriendsByStatus(string $status, ?int $churchId = null): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE role = 'member' AND status = ?";
        $params = [$status];
        
        if ($churchId) {
            $sql .= " AND church_id = ?";
            $params[] = $churchId;
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result['count'] ?? 0;
    }
    
    public function countAssignedFriends(?int $churchId = null): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} u 
                INNER JOIN hierarchy h ON u.id = h.user_id 
                WHERE u.role = 'member' AND u.status = 'pending'";
        $params = [];
        
        if ($churchId) {
            $sql .= " AND u.church_id = ?";
            $params[] = $churchId;
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result['count'] ?? 0;
    }
} 