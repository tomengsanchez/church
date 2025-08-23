<?php

namespace App\Models;

class SatelifeEventModel extends EventModel
{
    protected string $table = 'satelife_events';
    protected string $eventType = 'satelife';
    
    public function getAllEvents(): array
    {
        $sql = "SELECT e.*, u.name as created_by_name, c.name as church_name 
                FROM {$this->table} e 
                LEFT JOIN users u ON e.created_by = u.id 
                LEFT JOIN churches c ON e.church_id = c.id 
                ORDER BY e.event_date DESC, e.event_time ASC";
        return $this->db->fetchAll($sql);
    }
    
    public function getEventsByCoach(int $coachId): array
    {
        $sql = "SELECT e.*, u.name as created_by_name, c.name as church_name 
                FROM {$this->table} e 
                LEFT JOIN users u ON e.created_by = u.id 
                LEFT JOIN churches c ON e.church_id = c.id
                WHERE e.created_by = ? 
                ORDER BY e.event_date DESC, e.event_time ASC";
        return $this->db->fetchAll($sql, [$coachId]);
    }
    
    public function getEventsRelevantToCoach(int $coachId): array
    {
        // First, get the coach's church_id
        $coachSql = "SELECT church_id FROM users WHERE id = ?";
        $coach = $this->db->fetch($coachSql, [$coachId]);
        
        if (!$coach) {
            return [];
        }
        
        $churchId = $coach['church_id'];
        
        // Get events from the coach's church (both created by coach and others in the same church)
        $sql = "SELECT DISTINCT e.*, u.name as created_by_name, c.name as church_name 
                FROM {$this->table} e 
                LEFT JOIN users u ON e.created_by = u.id 
                LEFT JOIN churches c ON e.church_id = c.id
                WHERE e.church_id = ?
                ORDER BY e.event_date DESC, e.event_time ASC";
        return $this->db->fetchAll($sql, [$churchId]);
    }
    
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
    
    public function getEventWithDetails(int $id): array|false
    {
        $sql = "SELECT e.*, u.name as created_by_name, c.name as church_name,
                       coach.name as coach_name, coach.satelife_name as satelife_name
                FROM {$this->table} e 
                LEFT JOIN users u ON e.created_by = u.id 
                LEFT JOIN churches c ON e.church_id = c.id 
                LEFT JOIN event_attendees ea ON e.id = ea.event_id AND ea.event_type = 'satelife'
                LEFT JOIN hierarchy h ON ea.user_id = h.user_id
                LEFT JOIN hierarchy h2 ON h.parent_id = h2.user_id
                LEFT JOIN users coach ON h2.parent_id = coach.id AND coach.role = 'coach'
                WHERE e.id = ?
                GROUP BY e.id";
        return $this->db->fetch($sql, [$id]);
    }
} 