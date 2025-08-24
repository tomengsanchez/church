// Global Pagination JavaScript Functions

function performSearch() {
    const searchTerm = document.getElementById('search').value;
    const currentUrl = new URL(window.location);
    
    if (searchTerm.trim()) {
        currentUrl.searchParams.set('search', searchTerm);
    } else {
        currentUrl.searchParams.delete('search');
    }
    
    // Reset to first page when searching
    currentUrl.searchParams.set('page', '1');
    
    window.location.href = currentUrl.toString();
}

function changePageSize(size) {
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.set('per_page', size);
    currentUrl.searchParams.set('page', '1'); // Reset to first page
    
    window.location.href = currentUrl.toString();
}

function changeSort(field) {
    const currentUrl = new URL(window.location);
    const currentSort = currentUrl.searchParams.get('sort');
    const currentDirection = currentUrl.searchParams.get('direction') || 'asc';
    
    if (currentSort === field) {
        // Toggle direction if same field
        const newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
        currentUrl.searchParams.set('direction', newDirection);
    } else {
        // New field, default to asc
        currentUrl.searchParams.set('sort', field);
        currentUrl.searchParams.set('direction', 'asc');
    }
    
    // Reset to first page when sorting
    currentUrl.searchParams.set('page', '1');
    
    window.location.href = currentUrl.toString();
}

function toggleSortDirection() {
    const currentUrl = new URL(window.location);
    const currentDirection = currentUrl.searchParams.get('direction') || 'asc';
    const newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
    
    currentUrl.searchParams.set('direction', newDirection);
    currentUrl.searchParams.set('page', '1'); // Reset to first page
    
    window.location.href = currentUrl.toString();
}

function changeCoachFilter(coachId) {
    const currentUrl = new URL(window.location);
    
    if (coachId) {
        currentUrl.searchParams.set('coach_id', coachId);
    } else {
        currentUrl.searchParams.delete('coach_id');
    }
    
    currentUrl.searchParams.set('page', '1'); // Reset to first page
    window.location.href = currentUrl.toString();
}

// Add event listeners for search input
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    if (searchInput) {
        // Search on Enter key
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
        
        // Auto-search after typing (with delay)
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                performSearch();
            }, 500); // 500ms delay
        });
    }
    
    // Add click handlers for sortable headers
    const sortableHeaders = document.querySelectorAll('th[data-sort]');
    sortableHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const sortField = this.getAttribute('data-sort');
            changeSort(sortField);
        });
    });
});

// Function to get current URL parameters
function getUrlParameter(name) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(name);
}

// Function to update URL parameters
function updateUrlParameter(name, value) {
    const currentUrl = new URL(window.location);
    if (value) {
        currentUrl.searchParams.set(name, value);
    } else {
        currentUrl.searchParams.delete(name);
    }
    return currentUrl.toString();
}

// Function to clear all filters and search
function clearFilters() {
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.delete('search');
    currentUrl.searchParams.delete('sort');
    currentUrl.searchParams.delete('direction');
    currentUrl.searchParams.delete('page');
    currentUrl.searchParams.delete('per_page');
    
    window.location.href = currentUrl.toString();
}

// Function to export current filtered data
function exportData(format = 'csv') {
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.set('export', format);
    currentUrl.searchParams.set('per_page', '1000'); // Export all data
    
    window.location.href = currentUrl.toString();
}
