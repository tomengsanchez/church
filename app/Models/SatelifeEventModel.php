<?php

namespace App\Models;

use App\Core\Paginatable;

class SatelifeEventModel extends EventModel
{
    use Paginatable;
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
                LEFT JOIN users coach ON e.coach_id = coach.id
                WHERE e.id = ?";
        return $this->db->fetch($sql, [$id]);
    }

    public function getPaginatedEventsWithDetails(
        int $page = 1,
        int $perPage = 10,
        string $searchTerm = '',
        string $sortField = '',
        string $sortDirection = 'asc',
        array $additionalFilters = []
    ): array {
        $offset = ($page - 1) * $perPage;
        
        // Check if we need coach filter
        $hasCoachFilter = !empty($additionalFilters['coach_id']);
        
        // Build WHERE clause for search
        $whereClause = '';
        $whereParams = [];
        
        if (!empty($searchTerm)) {
            $whereClause = "WHERE (e.title LIKE ? OR e.description LIKE ? OR e.location LIKE ? OR u.name LIKE ? OR c.name LIKE ?)";
            $searchParam = "%{$searchTerm}%";
            $whereParams = [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam];
        }
        
        // Add additional filters
        if (!empty($additionalFilters)) {
            $filterConditions = [];
            foreach ($additionalFilters as $field => $value) {
                if ($value !== null && $value !== '') {
                    if ($field === 'coach_id') {
                        // Coach filter - filter events by the specific coach
                        $filterConditions[] = "e.coach_id = ?";
                        $whereParams[] = $value;
                    } else {
                        $filterConditions[] = "{$field} = ?";
                        $whereParams[] = $value;
                    }
                }
            }
            if (!empty($filterConditions)) {
                if (empty($whereClause)) {
                    $whereClause = "WHERE " . implode(' AND ', $filterConditions);
                } else {
                    $whereClause .= " AND " . implode(' AND ', $filterConditions);
                }
            }
        }
        
        // Build ORDER BY clause
        $orderByClause = '';
        $allowedSortFields = ['e.title', 'e.event_date', 'e.event_time', 'e.created_at', 'u.name', 'c.name'];
        if (!empty($sortField) && in_array($sortField, $allowedSortFields)) {
            $direction = strtoupper($sortDirection) === 'DESC' ? 'DESC' : 'ASC';
            $orderByClause = "ORDER BY {$sortField} {$direction}";
        } else {
            $orderByClause = "ORDER BY e.event_date DESC, e.event_time ASC";
        }
        
        // Get total count
        $countSql = "SELECT COUNT(DISTINCT e.id) as total FROM {$this->table} e 
                     LEFT JOIN users u ON e.created_by = u.id 
                     LEFT JOIN churches c ON e.church_id = c.id 
                     {$whereClause}";
        
        $totalResult = $this->db->fetch($countSql, $whereParams);
        $totalItems = $totalResult['total'] ?? 0;
        
        // Get paginated data
        $dataSql = "SELECT e.*, u.name as created_by_name, c.name as church_name,
                           coach.name as coach_name, coach.satelife_name as satelife_name
                    FROM {$this->table} e 
                    LEFT JOIN users u ON e.created_by = u.id 
                    LEFT JOIN churches c ON e.church_id = c.id 
                    LEFT JOIN users coach ON e.coach_id = coach.id
                    {$whereClause} 
                    {$orderByClause} 
                    LIMIT {$perPage} OFFSET {$offset}";
        
        $items = $this->db->fetchAll($dataSql, $whereParams);
        
        return [
            'items' => $items,
            'total' => $totalItems,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($totalItems / $perPage)
        ];
    }
} 