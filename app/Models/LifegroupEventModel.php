<?php

namespace App\Models;

class LifegroupEventModel extends EventModel
{
    protected string $table = 'lifegroup_events';
    protected string $eventType = 'lifegroup';
    
    public function getAllEvents(): array
    {
        $sql = "SELECT e.*, u.name as created_by_name, c.name as church_name 
                FROM {$this->table} e 
                LEFT JOIN users u ON e.created_by = u.id 
                LEFT JOIN churches c ON e.church_id = c.id 
                ORDER BY e.event_date DESC, e.event_time ASC";
        return $this->db->fetchAll($sql);
    }
    
    public function getEventsByMentor(int $mentorId): array
    {
        $sql = "SELECT e.*, u.name as created_by_name, c.name as church_name 
                FROM {$this->table} e 
                LEFT JOIN users u ON e.created_by = u.id 
                LEFT JOIN churches c ON e.church_id = c.id 
                WHERE e.created_by = ? 
                ORDER BY e.event_date DESC, e.event_time ASC";
        return $this->db->fetchAll($sql, [$mentorId]);
    }
} 