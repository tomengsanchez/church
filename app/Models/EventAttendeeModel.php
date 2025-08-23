<?php

namespace App\Models;

use App\Core\Model;

class EventAttendeeModel extends Model
{
    protected string $table = 'event_attendees';

    public function getAttendeesByEvent(string $eventType, int $eventId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE event_type = ? AND event_id = ?";
        return $this->db->fetchAll($sql, [$eventType, $eventId]);
    }

    public function setAttendedUsers(string $eventType, int $eventId, array $attendedUserIds): void
    {
        // Remove all existing attendees for this event
        $this->db->query("DELETE FROM {$this->table} WHERE event_type = ? AND event_id = ?", [$eventType, $eventId]);

        if (empty($attendedUserIds)) {
            return;
        }

        // Insert attended users
        foreach ($attendedUserIds as $userId) {
            $this->db->query(
                "INSERT INTO {$this->table} (event_type, event_id, user_id, status, created_at, updated_at) VALUES (?, ?, ?, 'attended', NOW(), NOW())",
                [$eventType, $eventId, (int)$userId]
            );
        }
    }

    public function getAttendeeUsers(string $eventType, int $eventId): array
    {
        $sql = "SELECT u.id, u.name, u.email, u.phone,
                       mentor.name as mentor_name,
                       coach.name as coach_name
                FROM {$this->table} ea
                INNER JOIN users u ON ea.user_id = u.id
                LEFT JOIN hierarchy h ON u.id = h.user_id
                LEFT JOIN users mentor ON h.parent_id = mentor.id AND mentor.role = 'mentor'
                LEFT JOIN hierarchy h2 ON mentor.id = h2.user_id
                LEFT JOIN users coach ON h2.parent_id = coach.id AND coach.role = 'coach'
                WHERE ea.event_type = ? AND ea.event_id = ? AND ea.status = 'attended'
                ORDER BY u.name ASC";
        return $this->db->fetchAll($sql, [$eventType, $eventId]);
    }

    public function deleteByEvent(string $eventType, int $eventId): void
    {
        $this->db->query("DELETE FROM {$this->table} WHERE event_type = ? AND event_id = ?", [$eventType, $eventId]);
    }
}


