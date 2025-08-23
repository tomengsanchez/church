<?php

namespace App\Models;

use App\Core\Model;

class MemberModel extends Model
{
    protected string $table = 'users';
    
    public function getMembersByChurch(int $churchId, ?string $status = null): array
    {
        $conditions = ['church_id' => $churchId, 'role' => 'member'];
        if ($status) {
            $conditions['status'] = $status;
        }
        return $this->findAll($conditions, 'name ASC');
    }
    
    public function getMembersByMentor(int $mentorId): array
    {
        $sql = "SELECT u.* FROM {$this->table} u 
                INNER JOIN hierarchy h ON u.id = h.user_id 
                WHERE h.parent_id = ? AND u.role = 'member' 
                ORDER BY u.name ASC";
        return $this->db->fetchAll($sql, [$mentorId]);
    }
    
    public function getMembersByCoach(int $coachId): array
    {
        $sql = "SELECT u.* FROM {$this->table} u 
                INNER JOIN hierarchy h1 ON u.id = h1.user_id
                INNER JOIN users m ON h1.parent_id = m.id AND m.role = 'mentor'
                INNER JOIN hierarchy h2 ON m.id = h2.user_id
                WHERE h2.parent_id = ? AND u.role = 'member' 
                ORDER BY u.name ASC";
        return $this->db->fetchAll($sql, [$coachId]);
    }
    
    public function getMembersByPastor(int $pastorId): array
    {
        $sql = "SELECT u.* FROM {$this->table} u 
                WHERE u.church_id = (SELECT church_id FROM users WHERE id = ?) 
                AND u.role = 'member' 
                ORDER BY u.name ASC";
        return $this->db->fetchAll($sql, [$pastorId]);
    }
    
    public function updateMemberStatus(int $id, string $status): bool
    {
        return $this->update($id, ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
    }
    
    public function getMemberWithHierarchy(int $id): array|false
    {
        $sql = "SELECT u.*, 
                ch.name as church_name,
                p.name as pastor_name,
                c.name as coach_name,
                m.name as mentor_name,
                l.name as lifegroup_name
                FROM {$this->table} u 
                LEFT JOIN churches ch ON u.church_id = ch.id
                LEFT JOIN users p ON ch.pastor_id = p.id
                LEFT JOIN hierarchy h1 ON u.id = h1.user_id
                LEFT JOIN users m ON h1.parent_id = m.id AND m.role = 'mentor'
                LEFT JOIN hierarchy h2 ON m.id = h2.user_id
                LEFT JOIN users c ON h2.parent_id = c.id AND c.role = 'coach'
                LEFT JOIN lifegroup_members lm ON u.id = lm.user_id AND lm.status = 'active'
                LEFT JOIN lifegroups l ON lm.lifegroup_id = l.id
                WHERE u.id = ?";
        return $this->db->fetch($sql, [$id]);
    }
    
    public function getAllMembersWithHierarchy(): array
    {
        $sql = "SELECT u.*, 
                ch.name as church_name,
                p.name as pastor_name,
                c.name as coach_name,
                m.name as mentor_name,
                l.name as lifegroup_name
                FROM {$this->table} u 
                LEFT JOIN churches ch ON u.church_id = ch.id
                LEFT JOIN users p ON ch.pastor_id = p.id
                LEFT JOIN hierarchy h1 ON u.id = h1.user_id
                LEFT JOIN users m ON h1.parent_id = m.id AND m.role = 'mentor'
                LEFT JOIN hierarchy h2 ON m.id = h2.user_id
                LEFT JOIN users c ON h2.parent_id = c.id AND c.role = 'coach'
                LEFT JOIN lifegroup_members lm ON u.id = lm.user_id AND lm.status = 'active'
                LEFT JOIN lifegroups l ON lm.lifegroup_id = l.id
                WHERE u.role = 'member'
                ORDER BY u.name ASC";
        return $this->db->fetchAll($sql);
    }
    
    public function getMemberStats(?int $churchId = null): array|false
    {
        $sql = "SELECT 
                COUNT(*) as total_members,
                SUM(CASE WHEN status NOT IN ('pending', 'new_friend') THEN 1 ELSE 0 END) as active_members,
                SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_members,
                SUM(CASE WHEN status IN ('pending', 'new_friend') THEN 1 ELSE 0 END) as in_progress_members
                FROM {$this->table} WHERE role = 'member'";
        
        if ($churchId) {
            $sql .= " AND church_id = ?";
            return $this->db->fetch($sql, [$churchId]);
        }
        
        return $this->db->fetch($sql);
    }
    
    public function searchMembers(string $search, ?int $churchId = null): array
    {
        $sql = "SELECT u.*, 
                ch.name as church_name,
                p.name as pastor_name,
                c.name as coach_name,
                m.name as mentor_name,
                l.name as lifegroup_name
                FROM {$this->table} u 
                LEFT JOIN churches ch ON u.church_id = ch.id
                LEFT JOIN users p ON ch.pastor_id = p.id
                LEFT JOIN hierarchy h1 ON u.id = h1.user_id
                LEFT JOIN users m ON h1.parent_id = m.id AND m.role = 'mentor'
                LEFT JOIN hierarchy h2 ON m.id = h2.user_id
                LEFT JOIN users c ON h2.parent_id = c.id AND c.role = 'coach'
                LEFT JOIN lifegroup_members lm ON u.id = lm.user_id AND lm.status = 'active'
                LEFT JOIN lifegroups l ON lm.lifegroup_id = l.id
                WHERE u.role = 'member' AND (u.name LIKE ? OR u.email LIKE ?)";
        $params = ["%$search%", "%$search%"];
        
        if ($churchId) {
            $sql .= " AND u.church_id = ?";
            $params[] = $churchId;
        }
        
        $sql .= " ORDER BY u.name ASC";
        return $this->db->fetchAll($sql, $params);
    }

    public function getMemberStatusPercentages(int $churchId): array
    {
        // First get total members count
        $totalSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE role = 'member' AND church_id = ?";
        $totalResult = $this->db->fetch($totalSql, [$churchId]);
        $totalMembers = $totalResult['total'] ?? 0;
        
        if ($totalMembers == 0) {
            return [];
        }
        
        $sql = "SELECT 
                ms.name as status_name,
                ms.slug as status_slug,
                ms.badge_class,
                COALESCE(COUNT(u.id), 0) as count,
                ROUND((COALESCE(COUNT(u.id), 0) * 100.0 / ?), 2) as percentage
                FROM member_statuses ms
                LEFT JOIN {$this->table} u ON ms.slug = u.status AND u.role = 'member' AND u.church_id = ?
                WHERE ms.is_active = 1
                GROUP BY ms.id, ms.name, ms.slug, ms.badge_class
                ORDER BY ms.sort_order ASC, ms.name ASC";
        
        return $this->db->fetchAll($sql, [$totalMembers, $churchId]);
    }

    public function getHierarchyStats(int $churchId): array
    {
        try {
            // Get coaches count
            $coachesSql = "SELECT COUNT(*) as count FROM {$this->table} WHERE role = 'coach' AND church_id = ?";
            $coachesResult = $this->db->fetch($coachesSql, [$churchId]);
            $coachesCount = $coachesResult['count'] ?? 0;

            // Get mentors count
            $mentorsSql = "SELECT COUNT(*) as count FROM {$this->table} WHERE role = 'mentor' AND church_id = ?";
            $mentorsResult = $this->db->fetch($mentorsSql, [$churchId]);
            $mentorsCount = $mentorsResult['count'] ?? 0;

            // Get lifegroups count
            $lifegroupsSql = "SELECT COUNT(*) as count FROM lifegroups WHERE church_id = ?";
            $lifegroupsResult = $this->db->fetch($lifegroupsSql, [$churchId]);
            $lifegroupsCount = $lifegroupsResult['count'] ?? 0;

            return [
                'coaches' => $coachesCount,
                'mentors' => $mentorsCount,
                'lifegroups' => $lifegroupsCount
            ];
        } catch (\Exception $e) {
            // Log error and return default values
            error_log("Error getting hierarchy stats: " . $e->getMessage());
            return [
                'coaches' => 0,
                'mentors' => 0,
                'lifegroups' => 0
            ];
        }
    }

    public function getCoachHierarchyDetails(int $coachId): array
    {
        try {
            // Get mentors under this coach
            $mentorsSql = "SELECT u.*, 
                          (SELECT COUNT(*) FROM hierarchy h WHERE h.parent_id = u.id) as member_count
                          FROM {$this->table} u 
                          INNER JOIN hierarchy h ON u.id = h.user_id 
                          WHERE h.parent_id = ? AND u.role = 'mentor' 
                          ORDER BY u.name ASC";
            $mentors = $this->db->fetchAll($mentorsSql, [$coachId]);

            // Get lifegroups under this coach (through mentors)
            $lifegroupsSql = "SELECT DISTINCT l.*, 
                             (SELECT COUNT(*) FROM lifegroup_members lm WHERE lm.lifegroup_id = l.id AND lm.status = 'active') as member_count
                             FROM lifegroups l 
                             INNER JOIN lifegroup_members lm ON l.id = lm.lifegroup_id 
                             INNER JOIN hierarchy h ON lm.user_id = h.user_id 
                             WHERE h.parent_id IN (
                                 SELECT u.id FROM {$this->table} u 
                                 INNER JOIN hierarchy h2 ON u.id = h2.user_id 
                                 WHERE h2.parent_id = ? AND u.role = 'mentor'
                             ) AND lm.status = 'active'
                             ORDER BY l.name ASC";
            $lifegroups = $this->db->fetchAll($lifegroupsSql, [$coachId]);

            return [
                'mentors' => $mentors ?: [],
                'lifegroups' => $lifegroups ?: []
            ];
        } catch (\Exception $e) {
            error_log("Error getting coach hierarchy details: " . $e->getMessage());
            return [
                'mentors' => [],
                'lifegroups' => []
            ];
        }
    }

    public function getMentorHierarchyDetails(int $mentorId): array
    {
        try {
            // Get lifegroups under this mentor
            $lifegroupsSql = "SELECT l.*, 
                             (SELECT COUNT(*) FROM lifegroup_members lm WHERE lm.lifegroup_id = l.id AND lm.status = 'active') as member_count
                             FROM lifegroups l 
                             INNER JOIN lifegroup_members lm ON l.id = lm.lifegroup_id 
                             WHERE lm.user_id IN (
                                 SELECT user_id FROM hierarchy WHERE parent_id = ?
                             ) AND lm.status = 'active'
                             ORDER BY l.name ASC";
            $lifegroups = $this->db->fetchAll($lifegroupsSql, [$mentorId]);

            return [
                'lifegroups' => $lifegroups ?: []
            ];
        } catch (\Exception $e) {
            error_log("Error getting mentor hierarchy details: " . $e->getMessage());
            return [
                'lifegroups' => []
            ];
        }
    }

    public function getLifegroupsWithHierarchy(int $churchId): array
    {
        try {
            $sql = "SELECT 
                    l.*,
                    (SELECT COUNT(*) FROM lifegroup_members lm WHERE lm.lifegroup_id = l.id AND lm.status = 'active') as member_count,
                    GROUP_CONCAT(DISTINCT m.name ORDER BY m.name SEPARATOR ', ') as mentor_names,
                    GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ') as coach_names
                    FROM lifegroups l
                    LEFT JOIN lifegroup_members lm ON l.id = lm.lifegroup_id AND lm.status = 'active'
                    LEFT JOIN hierarchy h ON lm.user_id = h.user_id
                    LEFT JOIN users m ON h.parent_id = m.id AND m.role = 'mentor'
                    LEFT JOIN hierarchy h2 ON m.id = h2.user_id
                    LEFT JOIN users c ON h2.parent_id = c.id AND c.role = 'coach'
                    WHERE l.church_id = ?
                    GROUP BY l.id, l.name, l.description, l.church_id, l.created_at, l.updated_at
                    ORDER BY l.name ASC";
            
            return $this->db->fetchAll($sql, [$churchId]) ?: [];
        } catch (\Exception $e) {
            error_log("Error getting lifegroups with hierarchy: " . $e->getMessage());
            return [];
        }
    }
} 