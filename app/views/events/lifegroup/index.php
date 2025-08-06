<?php $layout = 'layouts/authenticated'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">
                <i class="fas fa-users me-2"></i>Lifegroup Events
            </h1>
            <a href="/events/lifegroup/create" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add Lifegroup Event
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <?php if (!empty($events)): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Location</th>
                                <th>Church</th>
                                <th>Created By</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($events as $event): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($event['title'] ?? '') ?></strong>
                                    <?php if (!empty($event['description'])): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars(substr($event['description'], 0, 100)) ?><?= strlen($event['description']) > 100 ? '...' : '' ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('M j, Y', strtotime($event['event_date'])) ?></td>
                                <td><?= $event['event_time'] ? date('g:i A', strtotime($event['event_time'])) : 'TBD' ?></td>
                                <td><?= htmlspecialchars($event['location'] ?? 'TBD') ?></td>
                                <td><?= htmlspecialchars($event['church_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($event['created_by_name'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="badge bg-<?= $event['status'] === 'active' ? 'success' : ($event['status'] === 'cancelled' ? 'danger' : 'secondary') ?>">
                                        <?= ucfirst($event['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="/events/lifegroup/view/<?= $event['id'] ?>" class="btn btn-outline-primary btn-sm" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="/events/lifegroup/edit/<?= $event['id'] ?>" class="btn btn-outline-secondary btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteEvent(<?= $event['id'] ?>, '<?= htmlspecialchars($event['title'] ?? '') ?>')" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No Lifegroup Events Found</h4>
                    <p class="text-muted">Start by adding your first lifegroup event.</p>
                    <a href="/events/lifegroup/create" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add Lifegroup Event
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function deleteEvent(id, title) {
    if (confirm(`Are you sure you want to delete "${title}"? This action cannot be undone.`)) {
        // TODO: Implement delete functionality
        alert('Delete functionality will be implemented soon!');
    }
}
</script> 