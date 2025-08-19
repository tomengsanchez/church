<?php

namespace App\Models;

use App\Core\Model;

class MemberStatusModel extends Model
{
    protected string $table = 'member_statuses';

    public function getAllActive(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY sort_order ASC, name ASC";
        return $this->db->fetchAll($sql);
    }

    public function getAll(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY sort_order ASC, name ASC";
        return $this->db->fetchAll($sql);
    }

    public function findById(int $id): array|false
    {
        return $this->find($id);
    }

    public function createStatus(array $data): int|false
    {
        return $this->create([
            'slug' => $data['slug'],
            'name' => $data['name'],
            'is_active' => isset($data['is_active']) ? (int)$data['is_active'] : 1,
            'is_default' => isset($data['is_default']) ? (int)$data['is_default'] : 0,
            'sort_order' => isset($data['sort_order']) ? (int)$data['sort_order'] : 0,
        ]);
    }

    public function updateStatus(int $id, array $data): bool
    {
        return $this->update($id, [
            'slug' => $data['slug'],
            'name' => $data['name'],
            'is_active' => isset($data['is_active']) ? (int)$data['is_active'] : 1,
            'is_default' => isset($data['is_default']) ? (int)$data['is_default'] : 0,
            'sort_order' => isset($data['sort_order']) ? (int)$data['sort_order'] : 0,
        ]);
    }

    public function deleteStatus(int $id): bool
    {
        return $this->delete($id);
    }

    public function setDefault(int $id): bool
    {
        // Clear previous default
        $this->db->query("UPDATE {$this->table} SET is_default = 0");
        // Set new default
        return $this->update($id, ['is_default' => 1]);
    }

    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $sql = "SELECT id FROM {$this->table} WHERE slug = ?";
        $params = [$slug];
        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        return (bool)$this->db->fetch($sql, $params);
    }
}


