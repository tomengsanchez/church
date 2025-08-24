<?php

namespace App\Core;

trait Paginatable
{
    protected function buildSearchWhereClause(string $searchTerm, array $searchableFields): string
    {
        if (empty($searchTerm) || empty($searchableFields)) {
            return '';
        }

        $conditions = [];
        foreach ($searchableFields as $field) {
            $conditions[] = "{$field} LIKE ?";
        }

        return 'WHERE ' . implode(' OR ', $conditions);
    }

    protected function buildSearchParams(string $searchTerm, array $searchableFields): array
    {
        if (empty($searchTerm) || empty($searchableFields)) {
            return [];
        }

        $params = [];
        foreach ($searchableFields as $field) {
            $params[] = "%{$searchTerm}%";
        }

        return $params;
    }

    protected function buildOrderByClause(string $sortField, string $sortDirection, array $allowedSortFields): string
    {
        if (empty($sortField) || !in_array($sortField, $allowedSortFields)) {
            return '';
        }

        $direction = strtoupper($sortDirection) === 'DESC' ? 'DESC' : 'ASC';
        return "ORDER BY {$sortField} {$direction}";
    }

    public function getPaginatedData(
        int $page = 1,
        int $perPage = 10,
        string $searchTerm = '',
        string $sortField = '',
        string $sortDirection = 'asc',
        array $searchableFields = [],
        array $allowedSortFields = [],
        array $additionalFilters = []
    ): array {
        $offset = ($page - 1) * $perPage;
        
        // Build WHERE clause for search
        $searchWhere = $this->buildSearchWhereClause($searchTerm, $searchableFields);
        $searchParams = $this->buildSearchParams($searchTerm, $searchableFields);
        
        // Build WHERE clause for additional filters
        $filterWhere = '';
        $filterParams = [];
        if (!empty($additionalFilters)) {
            $filterConditions = [];
            foreach ($additionalFilters as $field => $value) {
                if ($value !== null && $value !== '') {
                    $filterConditions[] = "{$field} = ?";
                    $filterParams[] = $value;
                }
            }
            if (!empty($filterConditions)) {
                $filterWhere = 'WHERE ' . implode(' AND ', $filterConditions);
            }
        }
        
        // Combine WHERE clauses
        $whereClause = '';
        $whereParams = [];
        
        if (!empty($searchWhere) && !empty($filterWhere)) {
            $whereClause = str_replace('WHERE', 'AND', $searchWhere);
            $whereClause = $filterWhere . ' ' . $whereClause;
            $whereParams = array_merge($filterParams, $searchParams);
        } elseif (!empty($searchWhere)) {
            $whereClause = $searchWhere;
            $whereParams = $searchParams;
        } elseif (!empty($filterWhere)) {
            $whereClause = $filterWhere;
            $whereParams = $filterParams;
        }
        
        // Build ORDER BY clause
        $orderByClause = $this->buildOrderByClause($sortField, $sortDirection, $allowedSortFields);
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table}";
        if (!empty($whereClause)) {
            $countSql .= " {$whereClause}";
        }
        
        $totalResult = $this->db->fetch($countSql, $whereParams);
        $totalItems = $totalResult['total'] ?? 0;
        
        // Get paginated data
        $dataSql = "SELECT * FROM {$this->table}";
        if (!empty($whereClause)) {
            $dataSql .= " {$whereClause}";
        }
        if (!empty($orderByClause)) {
            $dataSql .= " {$orderByClause}";
        }
        $dataSql .= " LIMIT {$perPage} OFFSET {$offset}";
        
        $items = $this->db->fetchAll($dataSql, $whereParams);
        
        return [
            'items' => $items,
            'total' => $totalItems,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($totalItems / $perPage)
        ];
    }

    public function getPaginatedDataWithJoins(
        string $selectClause,
        string $joinClause,
        int $page = 1,
        int $perPage = 10,
        string $searchTerm = '',
        string $sortField = '',
        string $sortDirection = 'asc',
        array $searchableFields = [],
        array $allowedSortFields = [],
        array $additionalFilters = []
    ): array {
        $offset = ($page - 1) * $perPage;
        
        // Build WHERE clause for search
        $searchWhere = $this->buildSearchWhereClause($searchTerm, $searchableFields);
        $searchParams = $this->buildSearchParams($searchTerm, $searchableFields);
        
        // Build WHERE clause for additional filters
        $filterWhere = '';
        $filterParams = [];
        if (!empty($additionalFilters)) {
            $filterConditions = [];
            foreach ($additionalFilters as $field => $value) {
                if ($value !== null && $value !== '') {
                    $filterConditions[] = "{$field} = ?";
                    $filterParams[] = $value;
                }
            }
            if (!empty($filterConditions)) {
                $filterWhere = 'WHERE ' . implode(' AND ', $filterConditions);
            }
        }
        
        // Combine WHERE clauses
        $whereClause = '';
        $whereParams = [];
        
        if (!empty($searchWhere) && !empty($filterWhere)) {
            $whereClause = str_replace('WHERE', 'AND', $searchWhere);
            $whereClause = $filterWhere . ' ' . $whereClause;
            $whereParams = array_merge($filterParams, $searchParams);
        } elseif (!empty($searchWhere)) {
            $whereClause = $searchWhere;
            $whereParams = $searchParams;
        } elseif (!empty($filterWhere)) {
            $whereClause = $filterWhere;
            $whereParams = $filterParams;
        }
        
        // Build ORDER BY clause
        $orderByClause = $this->buildOrderByClause($sortField, $sortDirection, $allowedSortFields);
        
        // Get total count - for COUNT queries with JOINs, we need to use a subquery approach
        if (!empty($joinClause)) {
            // Use a subquery to count distinct IDs from the main table
            $countSql = "SELECT COUNT(*) as total FROM (
                SELECT DISTINCT {$this->table}.id 
                FROM {$this->table} {$joinClause}";
            if (!empty($whereClause)) {
                $countSql .= " {$whereClause}";
            }
            $countSql .= ") as count_subquery";
        } else {
            $countSql = "SELECT COUNT(*) as total FROM {$this->table}";
            if (!empty($whereClause)) {
                $countSql .= " {$whereClause}";
            }
        }
        
        $totalResult = $this->db->fetch($countSql, $whereParams);
        $totalItems = $totalResult['total'] ?? 0;
        
        // Get paginated data
        $dataSql = "SELECT DISTINCT {$selectClause} FROM {$this->table} {$joinClause}";
        if (!empty($whereClause)) {
            $dataSql .= " {$whereClause}";
        }
        if (!empty($orderByClause)) {
            $dataSql .= " {$orderByClause}";
        }
        $dataSql .= " LIMIT {$perPage} OFFSET {$offset}";
        
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
