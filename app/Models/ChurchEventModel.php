<?php

namespace App\Models;

class ChurchEventModel extends EventModel
{
    protected string $table = 'church_events';
    protected string $eventType = 'church';
    
    public function getAllEvents(): array
    {
        $sql = "SELECT e.*, u.name as created_by_name, c.name as church_name 
                FROM {$this->table} e 
                LEFT JOIN users u ON e.created_by = u.id 
                LEFT JOIN churches c ON e.church_id = c.id 
                ORDER BY e.event_date DESC, e.event_time ASC";
        return $this->db->fetchAll($sql);
    }
    
    public function getEventsByPastor(int $pastorId): array
    {
        $sql = "SELECT e.*, u.name as created_by_name, c.name as church_name 
                FROM {$this->table} e 
                LEFT JOIN users u ON e.created_by = u.id 
                LEFT JOIN churches c ON e.church_id = c.id 
                WHERE e.church_id = (SELECT church_id FROM users WHERE id = ?) 
                ORDER BY e.event_date DESC, e.event_time ASC";
        return $this->db->fetchAll($sql, [$pastorId]);
    }
} 