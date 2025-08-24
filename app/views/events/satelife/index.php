<?php $layout = 'layouts/authenticated'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">
                <i class="fas fa-calendar-alt me-2"></i><?= $_SESSION['user_role'] === 'coach' ? 'My Satelife Events' : 'Satelife Events' ?>
            </h1>
            <a href="/events/satelife/create" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add Event
            </a>
                                </div>
                    </div>
                    </div>
                </div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i><?= $_SESSION['user_role'] === 'coach' ? 'My Satelife Events' : 'All Satelife Events' ?>
                </h5>
            </div>
            <div class="card-body">
                <!-- Search, Filter, and Pagination Controls -->
                <div class="filter-controls">
                    <div class="row">
                        <div class="<?= $_SESSION['user_role'] === 'coach' ? 'col-md-5' : 'col-md-4' ?>">
                            <!-- Search -->
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="search" placeholder="Search all fields..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                                <button class="btn btn-outline-secondary" type="button" onclick="performSearch()">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                        <?php if ($_SESSION['user_role'] !== 'coach'): ?>
                        <div class="col-md-3">
                            <!-- Coach Filter -->
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user-graduate"></i></span>
                                <select class="form-select" id="coach_filter" onchange="changeCoachFilter(this.value)">
                                    <option value="">All Coaches</option>
                                    <?php foreach ($coaches as $coach): ?>
                                        <option value="<?= $coach['id'] ?>" <?= ($_GET['coach_id'] ?? '') == $coach['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($coach['name']) ?>
                                            <?php if (!empty($coach['satelife_name'])): ?>
                                                (<?= htmlspecialchars($coach['satelife_name']) ?>)
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="<?= $_SESSION['user_role'] === 'coach' ? 'col-md-4' : 'col-md-3' ?>">
                            <!-- Page Size -->
                            <div class="input-group">
                                <span class="input-group-text">Show</span>
                                <select class="form-select" onchange="changePageSize(this.value)">
                                    <?php
                                    $currentPerPage = (int)($_GET['per_page'] ?? 10);
                                    $pageSizes = [5, 10, 50, 100, 200, 500];
                                    foreach ($pageSizes as $size):
                                    ?>
                                        <option value="<?= $size ?>" <?= $currentPerPage == $size ? 'selected' : '' ?>><?= $size ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="input-group-text">per page</span>
                            </div>
                        </div>
                        <div class="<?= $_SESSION['user_role'] === 'coach' ? 'col-md-3' : 'col-md-2' ?>">
                            <!-- Sort Direction Toggle -->
                            <button class="btn btn-outline-secondary w-100" type="button" onclick="toggleSortDirection()">
                                <i class="fas fa-sort-<?= strtolower($_GET['direction'] ?? 'asc') == 'desc' ? 'down' : 'up' ?>"></i>
                                <?= strtoupper($_GET['direction'] ?? 'asc') ?>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th data-sort="e.title" style="cursor: pointer;">
                                    Title
                                    <i class="fas fa-sort"></i>
                                </th>
                                <th data-sort="e.event_date" style="cursor: pointer;">
                                    Date & Time
                                    <i class="fas fa-sort"></i>
                                </th>
                                <th data-sort="e.location" style="cursor: pointer;">
                                    Location
                                    <i class="fas fa-sort"></i>
                                </th>
                                <th data-sort="c.name" style="cursor: pointer;">
                                    Church
                                    <i class="fas fa-sort"></i>
                                </th>
                                <th data-sort="coach.name" style="cursor: pointer;">
                                    Coach
                                    <i class="fas fa-sort"></i>
                                </th>
                                <th data-sort="u.name" style="cursor: pointer;">
                                    Created By
                                    <i class="fas fa-sort"></i>
                                </th>
                                <th data-sort="e.status" style="cursor: pointer;">
                                    Status
                                    <i class="fas fa-sort"></i>
                                </th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($events)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-calendar-times fa-2x text-muted mb-2 d-block"></i>
                                        <h6 class="text-muted">No Satelife Events Found</h6>
                                        <p class="text-muted mb-0">No events match your current search criteria.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($events as $event): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($event['title']) ?></strong>
                                        <?php if ($event['description']): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars(substr($event['description'], 0, 50)) ?><?= strlen($event['description']) > 50 ? '...' : '' ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div><?= date('M j, Y', strtotime($event['event_date'])) ?></div>
                                        <?php if ($event['event_time']): ?>
                                            <small class="text-muted"><?= date('g:i A', strtotime($event['event_time'])) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($event['location']): ?>
                                            <i class="fas fa-map-marker-alt me-1 text-muted"></i>
                                            <?= htmlspecialchars($event['location']) ?>
                                        <?php else: ?>
                                            <span class="text-muted">Not specified</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?= htmlspecialchars($event['church_name']) ?></span>
                                    </td>
                                    <td>
                                        <i class="fas fa-user-graduate me-1 text-muted"></i>
                                        <?= htmlspecialchars($event['coach_name'] ?? 'Not assigned') ?>
                                        <?php if (!empty($event['satelife_name'])): ?>
                                            <br><small class="text-muted">(<?= htmlspecialchars($event['satelife_name']) ?>)</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <i class="fas fa-user me-1 text-muted"></i>
                                        <?= htmlspecialchars($event['created_by_name']) ?>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = match($event['status']) {
                                            'active' => 'success',
                                            'cancelled' => 'danger',
                                            'completed' => 'secondary',
                                            default => 'warning'
                                        };
                                        ?>
                                        <span class="badge bg-<?= $statusClass ?>">
                                            <?= ucfirst($event['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="/events/satelife/view/<?= $event['id'] ?>" class="btn btn-sm btn-outline-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="/events/satelife/edit/<?= $event['id'] ?>" class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="/events/satelife/duplicate/<?= $event['id'] ?>" method="POST" class="d-inline" onsubmit="return confirm('Duplicate this event?')">
                                                <button type="submit" class="btn btn-sm btn-outline-info" title="Duplicate">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </form>
                                            <form action="/events/satelife/delete/<?= $event['id'] ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this event?')">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination Numbers -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        Showing <?= (($pagination->getCurrentPage() - 1) * $pagination->getPerPage()) + 1 ?> to <?= min($pagination->getCurrentPage() * $pagination->getPerPage(), $pagination->getTotalItems()) ?> of <?= $pagination->getTotalItems() ?> results
                    </div>
                    <?= $pagination->renderPaginationControls() ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// JavaScript functions for filter controls
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

function changeCoachFilter(coachId) {
    const currentUrl = new URL(window.location);
    
    if (coachId) {
        currentUrl.searchParams.set('coach_id', coachId);
    } else {
        currentUrl.searchParams.delete('coach_id');
    }
    
    // Reset to first page when filtering
    currentUrl.searchParams.set('page', '1');
    
    window.location.href = currentUrl.toString();
}

function changePageSize(pageSize) {
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.set('per_page', pageSize);
    currentUrl.searchParams.set('page', '1'); // Reset to first page
    
    window.location.href = currentUrl.toString();
}

function toggleSortDirection() {
    const currentUrl = new URL(window.location);
    const currentDirection = currentUrl.searchParams.get('direction') || 'asc';
    const newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
    
    currentUrl.searchParams.set('direction', newDirection);
    
    window.location.href = currentUrl.toString();
}

// Add event listener for Enter key on search input
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
    }
});
</script>