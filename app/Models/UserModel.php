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