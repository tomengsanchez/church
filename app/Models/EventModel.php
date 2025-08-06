<?php

namespace App\Models;

use App\Core\Model;

abstract class EventModel extends Model
{
    protected string $table;
    protected string $eventType;
    
    public function getEventsByChurch(int $churchId): array
    {
        $sql = "SELECT e.*, u.name as created_by_name, c.name as church_name 
                FROM {$this->table} e 
                LEFT JOIN users u ON e.created_by = u.id 
                LEFT JOIN churches c ON e.church_id = c.id 
                WHERE e.church_id = ? 
                ORDER BY e.event_date DESC, e.event_time ASC";
        return $this->db->fetchAll($sql, [$churchId]);
    }
    
    public function getEventsByCreator(int $userId): array
    {
        $sql = "SELECT e.*, u.name as created_by_name, c.name as church_name 
                FROM {$this->table} e 
                LEFT JOIN users u ON e.created_by = u.id 
                LEFT JOIN churches c ON e.church_id = c.id 
                WHERE e.created_by = ? 
                ORDER BY e.event_date DESC, e.event_time ASC";
        return $this->db->fetchAll($sql, [$userId]);
    }
    
    public function getEventWithDetails(int $id): array|false
    {
        $sql = "SELECT e.*, u.name as created_by_name, c.name as church_name 
                FROM {$this->table} e 
                LEFT JOIN users u ON e.created_by = u.id 
                LEFT JOIN churches c ON e.church_id = c.id 
                WHERE e.id = ?";
        return $this->db->fetch($sql, [$id]);
    }
    
    public function getUpcomingEvents(int $churchId, int $limit = 10): array
    {
        $sql = "SELECT e.*, u.name as created_by_name, c.name as church_name 
                FROM {$this->table} e 
                LEFT JOIN users u ON e.created_by = u.id 
                LEFT JOIN churches c ON e.church_id = c.id 
                WHERE e.church_id = ? AND e.event_date >= CURDATE() AND e.status = 'active' 
                ORDER BY e.event_date ASC, e.event_time ASC 
                LIMIT ?";
        return $this->db->fetchAll($sql, [$churchId, $limit]);
    }
    
    public function updateEventStatus(int $id, string $status): bool
    {
        return $this->update($id, ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
    }
} 