<?php $layout = 'layouts/authenticated'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">
                <i class="fas fa-calendar-alt me-2"></i>Satelife Events
            </h1>
            <a href="/events/satelife/create" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add Event
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>All Satelife Events
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($events)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Satelife Events Found</h5>
                        <p class="text-muted">Start by creating your first satelife event.</p>
                        <a href="/events/satelife/create" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Create Event
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Date & Time</th>
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
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div> 