<?php

namespace App\Core;

class Pagination
{
    private int $currentPage;
    private int $perPage;
    private int $totalItems;
    private int $totalPages;
    private array $items;
    private string $searchTerm;
    private string $sortField;
    private string $sortDirection;
    private array $filters;
    private array $availablePageSizes;
    private string $baseUrl;

    public function __construct(
        array $items,
        int $currentPage = 1,
        int $perPage = 10,
        int $totalItems = 0,
        string $searchTerm = '',
        string $sortField = '',
        string $sortDirection = 'asc',
        array $filters = [],
        string $baseUrl = ''
    ) {
        $this->items = $items;
        $this->currentPage = max(1, $currentPage);
        $this->perPage = $perPage;
        $this->totalItems = $totalItems;
        $this->totalPages = ceil($totalItems / $perPage);
        $this->searchTerm = $searchTerm;
        $this->sortField = $sortField;
        $this->sortDirection = $sortDirection;
        $this->filters = $filters;
        $this->availablePageSizes = [5, 10, 50, 100, 200, 500];
        $this->baseUrl = $baseUrl;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getTotalItems(): int
    {
        return $this->totalItems;
    }

    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    public function getSearchTerm(): string
    {
        return $this->searchTerm;
    }

    public function getSortField(): string
    {
        return $this->sortField;
    }

    public function getSortDirection(): string
    {
        return $this->sortDirection;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getAvailablePageSizes(): array
    {
        return $this->availablePageSizes;
    }

    public function hasNextPage(): bool
    {
        return $this->currentPage < $this->totalPages;
    }

    public function hasPreviousPage(): bool
    {
        return $this->currentPage > 1;
    }

    public function getNextPage(): int
    {
        return min($this->currentPage + 1, $this->totalPages);
    }

    public function getPreviousPage(): int
    {
        return max($this->currentPage - 1, 1);
    }

    public function getFirstPage(): int
    {
        return 1;
    }

    public function getLastPage(): int
    {
        return $this->totalPages;
    }

    public function getStartItem(): int
    {
        return ($this->currentPage - 1) * $this->perPage + 1;
    }

    public function getEndItem(): int
    {
        return min($this->currentPage * $this->perPage, $this->totalItems);
    }

    public function buildUrl(int $page, int $perPage = null, string $search = null, string $sortField = null, string $sortDirection = null): string
    {
        $params = [];
        
        if ($page > 1) {
            $params['page'] = $page;
        }
        
        if ($perPage && $perPage !== 10) {
            $params['per_page'] = $perPage;
        }
        
        if ($search) {
            $params['search'] = $search;
        }
        
        if ($sortField) {
            $params['sort'] = $sortField;
        }
        
        if ($sortDirection && $sortDirection !== 'asc') {
            $params['direction'] = $sortDirection;
        }

        // Add existing filters
        foreach ($this->filters as $key => $value) {
            if ($value !== null && $value !== '') {
                $params[$key] = $value;
            }
        }

        if (empty($params)) {
            return $this->baseUrl;
        }

        return $this->baseUrl . '?' . http_build_query($params);
    }

    public function renderPaginationControls(): string
    {
        if ($this->totalPages <= 1) {
            return '';
        }

        $html = '<div class="d-flex justify-content-between align-items-center mt-4">';
        
        // Results info
        $html .= '<div class="text-muted">';
        $html .= "Showing {$this->getStartItem()} to {$this->getEndItem()} of {$this->totalItems} results";
        $html .= '</div>';

        // Pagination controls
        $html .= '<nav aria-label="Pagination">';
        $html .= '<ul class="pagination mb-0">';

        // First page
        if ($this->hasPreviousPage()) {
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . $this->buildUrl(1) . '" aria-label="First">';
            $html .= '<i class="fas fa-angle-double-left"></i>';
            $html .= '</a></li>';
        } else {
            $html .= '<li class="page-item disabled">';
            $html .= '<span class="page-link"><i class="fas fa-angle-double-left"></i></span>';
            $html .= '</li>';
        }

        // Previous page
        if ($this->hasPreviousPage()) {
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . $this->buildUrl($this->getPreviousPage()) . '" aria-label="Previous">';
            $html .= '<i class="fas fa-angle-left"></i>';
            $html .= '</a></li>';
        } else {
            $html .= '<li class="page-item disabled">';
            $html .= '<span class="page-link"><i class="fas fa-angle-left"></i></span>';
            $html .= '</li>';
        }

        // Page numbers
        $startPage = max(1, $this->currentPage - 2);
        $endPage = min($this->totalPages, $this->currentPage + 2);

        if ($startPage > 1) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }

        for ($i = $startPage; $i <= $endPage; $i++) {
            if ($i == $this->currentPage) {
                $html .= '<li class="page-item active">';
                $html .= '<span class="page-link">' . $i . '</span>';
                $html .= '</li>';
            } else {
                $html .= '<li class="page-item">';
                $html .= '<a class="page-link" href="' . $this->buildUrl($i) . '">' . $i . '</a>';
                $html .= '</li>';
            }
        }

        if ($endPage < $this->totalPages) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }

        // Next page
        if ($this->hasNextPage()) {
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . $this->buildUrl($this->getNextPage()) . '" aria-label="Next">';
            $html .= '<i class="fas fa-angle-right"></i>';
            $html .= '</a></li>';
        } else {
            $html .= '<li class="page-item disabled">';
            $html .= '<span class="page-link"><i class="fas fa-angle-right"></i></span>';
            $html .= '</li>';
        }

        // Last page
        if ($this->hasNextPage()) {
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . $this->buildUrl($this->totalPages) . '" aria-label="Last">';
            $html .= '<i class="fas fa-angle-double-right"></i>';
            $html .= '</a></li>';
        } else {
            $html .= '<li class="page-item disabled">';
            $html .= '<span class="page-link"><i class="fas fa-angle-double-right"></i></span>';
            $html .= '</li>';
        }

        $html .= '</ul>';
        $html .= '</nav>';
        $html .= '</div>';

        return $html;
    }

    public function renderSearchAndControls(): string
    {
        $html = '<div class="row mb-3">';
        
        // Search box
        $html .= '<div class="col-md-4">';
        $html .= '<div class="input-group">';
        $html .= '<span class="input-group-text"><i class="fas fa-search"></i></span>';
        $html .= '<input type="text" class="form-control" id="search" name="search" placeholder="Search all fields..." value="' . htmlspecialchars($this->searchTerm) . '">';
        $html .= '<button class="btn btn-outline-secondary" type="button" onclick="performSearch()">Search</button>';
        $html .= '</div>';
        $html .= '</div>';

        // Page size selector
        $html .= '<div class="col-md-2">';
        $html .= '<div class="input-group">';
        $html .= '<span class="input-group-text">Show</span>';
        $html .= '<select class="form-select" id="per_page" onchange="changePageSize(this.value)">';
        foreach ($this->availablePageSizes as $size) {
            $selected = $size == $this->perPage ? 'selected' : '';
            $html .= "<option value=\"{$size}\" {$selected}>{$size}</option>";
        }
        $html .= '</select>';
        $html .= '<span class="input-group-text">per page</span>';
        $html .= '</div>';
        $html .= '</div>';

        // Sort direction toggle
        $html .= '<div class="col-md-2">';
        $html .= '<div class="d-flex justify-content-end">';
        $html .= '<button class="btn btn-outline-secondary" type="button" onclick="toggleSortDirection()">';
        $html .= '<i class="fas fa-sort-' . ($this->sortDirection === 'desc' ? 'down' : 'up') . '"></i> ';
        $html .= ucfirst($this->sortDirection);
        $html .= '</button>';
        $html .= '</div>';
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }
}
