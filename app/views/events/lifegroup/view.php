<?php $layout = 'layouts/authenticated'; ?>

<div class="row">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Lifegroup Event Details</h5>
                <div class="d-flex gap-2">
                    <form action="/events/lifegroup/duplicate/<?= (int)$event['id'] ?>" method="post" class="d-inline">
                        <button type="submit" class="btn btn-sm btn-outline-success" title="Duplicate for next week">
                            <i class="fas fa-copy me-1"></i> Duplicate
                        </button>
                    </form>
                    <a href="/events/lifegroup" class="btn btn-sm btn-outline-secondary">Back</a>
                </div>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Title</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars($event['title']) ?></dd>

                    <dt class="col-sm-3">Date</dt>
                    <dd class="col-sm-9"><?= date('M j, Y', strtotime($event['event_date'])) ?></dd>

                    <dt class="col-sm-3">Time</dt>
                    <dd class="col-sm-9"><?= $event['event_time'] ? date('g:i A', strtotime($event['event_time'])) : 'TBD' ?></dd>

                    <dt class="col-sm-3">Location</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars($event['location'] ?? 'TBD') ?></dd>

                    <dt class="col-sm-3">Church</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars($event['church_name'] ?? 'N/A') ?></dd>

                    <dt class="col-sm-3">Created By</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars($event['created_by_name'] ?? 'N/A') ?></dd>

                    <dt class="col-sm-3">Status</dt>
                    <dd class="col-sm-9">
                        <span class="badge bg-<?= $event['status'] === 'active' ? 'success' : ($event['status'] === 'cancelled' ? 'danger' : 'secondary') ?>"><?= ucfirst($event['status']) ?></span>
                    </dd>

                    <?php if (!empty($event['description'])): ?>
                    <dt class="col-sm-3">Description</dt>
                    <dd class="col-sm-9"><?= nl2br(htmlspecialchars($event['description'])) ?></dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>
    </div>
</div>


