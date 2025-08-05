<?php
$layout = 'layouts/authenticated';
$title = 'Error Logs';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-exclamation-triangle text-danger me-2"></i>Error Logs
        </h1>
        <div class="btn-group">
            <a href="/errorlog/stats" class="btn btn-info btn-sm">
                <i class="fas fa-chart-bar me-1"></i>Statistics
            </a>
            <a href="/errorlog/export" class="btn btn-success btn-sm">
                <i class="fas fa-download me-1"></i>Export
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="level" class="form-label">Error Level</label>
                    <select name="level" id="level" class="form-select">
                        <option value="">All Levels</option>
                        <?php foreach ($errorLevels as $key => $label): ?>
                            <option value="<?= $key ?>" <?= ($filters['level'] === $key) ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" name="search" id="search" class="form-control" 
                           value="<?= htmlspecialchars($filters['search']) ?>" 
                           placeholder="Search in messages...">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                    <a href="/errorlog" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i>Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Error Logs (<?= $pagination['total_records'] ?> entries)</h5>
            <div class="btn-group">
                <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#clearOldModal">
                    <i class="fas fa-clock me-1"></i>Clear Old
                </button>
                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#clearAllModal">
                    <i class="fas fa-trash me-1"></i>Clear All
                </button>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($logs)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-info-circle text-muted fa-3x mb-3"></i>
                    <h5 class="text-muted">No error logs found</h5>
                    <p class="text-muted">No error logs match your current filters.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Level</th>
                                <th>User</th>
                                <th>IP Address</th>
                                <th>Request</th>
                                <th>Message</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $index => $log): ?>
                                <tr>
                                    <td>
                                        <small class="text-muted">
                                            <?= htmlspecialchars($log['timestamp']) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php
                                        $levelClass = match($log['level']) {
                                            'emergency', 'alert', 'critical' => 'danger',
                                            'error' => 'danger',
                                            'warning' => 'warning',
                                            'notice' => 'info',
                                            'info' => 'info',
                                            'debug' => 'secondary',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge bg-<?= $levelClass ?>">
                                            <?= strtoupper($log['level']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small><?= htmlspecialchars($log['user_id']) ?></small>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?= htmlspecialchars($log['ip_address']) ?></small>
                                    </td>
                                    <td>
                                        <small>
                                            <code><?= htmlspecialchars($log['request_method']) ?> <?= htmlspecialchars($log['request_uri']) ?></code>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 300px;" title="<?= htmlspecialchars($log['message']) ?>">
                                            <?= htmlspecialchars($log['message']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="/errorlog/view/<?= $index ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($pagination['total_pages'] > 1): ?>
                    <nav aria-label="Error logs pagination">
                        <ul class="pagination justify-content-center">
                            <?php if ($pagination['current_page'] > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $pagination['current_page'] - 1 ?>&level=<?= $filters['level'] ?>&search=<?= urlencode($filters['search']) ?>">
                                        Previous
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                                <li class="page-item <?= ($i === $pagination['current_page']) ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&level=<?= $filters['level'] ?>&search=<?= urlencode($filters['search']) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $pagination['current_page'] + 1 ?>&level=<?= $filters['level'] ?>&search=<?= urlencode($filters['search']) ?>">
                                        Next
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Clear Old Logs Modal -->
<div class="modal fade" id="clearOldModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Clear Old Log Files</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/errorlog/clear-old">
                <div class="modal-body">
                    <p>This will delete old log backup files that are older than the specified number of days.</p>
                    <div class="mb-3">
                        <label for="days_old" class="form-label">Days Old</label>
                        <input type="number" name="days_old" id="days_old" class="form-control" value="30" min="1" max="365">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Clear Old Files</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Clear All Logs Modal -->
<div class="modal fade" id="clearAllModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Clear All Error Logs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action will permanently delete all error logs. This action cannot be undone.
                </div>
                <p>Are you sure you want to clear all error logs?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="/errorlog/clear-all" class="d-inline">
                    <button type="submit" class="btn btn-danger">Clear All Logs</button>
                </form>
            </div>
        </div>
    </div>
</div> 