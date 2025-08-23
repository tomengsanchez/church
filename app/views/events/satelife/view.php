<?php $layout = 'layouts/authenticated'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">
                <i class="fas fa-calendar-check me-2"></i>Satelife Event Details
            </h1>
            <div>
                <a href="/events/satelife/edit/<?= $event['id'] ?>" class="btn btn-warning me-2">
                    <i class="fas fa-edit me-2"></i>Edit Event
                </a>
                <a href="/events/satelife" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Events
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Event Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted">Event Title</h6>
                        <p class="h5"><?= htmlspecialchars($event['title']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Church</h6>
                        <p><span class="badge bg-info"><?= htmlspecialchars($event['church_name']) ?></span></p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted">Coach/Satelife</h6>
                        <?php if (!empty($event['coach_name'])): ?>
                            <p><span class="badge bg-primary"><?= htmlspecialchars($event['coach_name']) ?> (<?= htmlspecialchars($event['satelife_name'] ?? 'Satelife') ?>)</span></p>
                        <?php else: ?>
                            <p><span class="badge bg-secondary"><?= htmlspecialchars($event['created_by_name']) ?></span></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted">Event Date</h6>
                        <p><i class="fas fa-calendar me-2"></i><?= date('l, F j, Y', strtotime($event['event_date'])) ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Event Time</h6>
                        <p>
                            <?php if ($event['event_time']): ?>
                                <i class="fas fa-clock me-2"></i><?= date('g:i A', strtotime($event['event_time'])) ?>
                            <?php else: ?>
                                <span class="text-muted">Not specified</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <?php if ($event['location']): ?>
                <div class="row">
                    <div class="col-12">
                        <h6 class="text-muted">Location</h6>
                        <p><i class="fas fa-map-marker-alt me-2"></i><?= htmlspecialchars($event['location']) ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($event['description']): ?>
                <div class="row">
                    <div class="col-12">
                        <h6 class="text-muted">Description</h6>
                        <p><?= nl2br(htmlspecialchars($event['description'])) ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted">Created By</h6>
                        <p><i class="fas fa-user me-2"></i><?= htmlspecialchars($event['created_by_name']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Status</h6>
                        <?php
                        $statusClass = match($event['status']) {
                            'active' => 'success',
                            'cancelled' => 'danger',
                            'completed' => 'secondary',
                            default => 'warning'
                        };
                        ?>
                        <p><span class="badge bg-<?= $statusClass ?>"><?= ucfirst($event['status']) ?></span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-users me-2"></i>Attendance Summary
                </h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <h3 class="text-primary"><?= count($attendees) ?></h3>
                    <p class="text-muted">Members Attended</p>
                </div>
                
                <?php if (empty($attendees)): ?>
                    <div class="text-center">
                        <i class="fas fa-users-slash fa-2x text-muted mb-2"></i>
                        <p class="text-muted">No attendance recorded yet</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($attendees as $attendee): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?= htmlspecialchars($attendee['name']) ?></strong>
                                    <br>
                                    <small class="text-muted"><?= htmlspecialchars($attendee['email']) ?></small>
                                </div>
                                <span class="badge bg-success">Attended</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Detailed Attendance List
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($attendees)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Attendance Records</h5>
                        <p class="text-muted">Attendance will appear here once members are marked as attended.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Mentor</th>
                                    <th>Coach</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attendees as $attendee): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($attendee['name']) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($attendee['email']) ?></td>
                                    <td>
                                        <?php if (!empty($attendee['mentor_name'])): ?>
                                            <span class="badge bg-info"><?= htmlspecialchars($attendee['mentor_name']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">Not assigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($attendee['coach_name'])): ?>
                                            <span class="badge bg-primary"><?= htmlspecialchars($attendee['coach_name']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">Not assigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">Attended</span>
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
