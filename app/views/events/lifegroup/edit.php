<?php $layout = 'layouts/authenticated'; ?>

<div class="row">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Edit Lifegroup Event</h5>
                <a href="/events/lifegroup" class="btn btn-sm btn-outline-secondary">Back</a>
            </div>
            <div class="card-body">
                <form action="/events/lifegroup/edit/<?= (int)$event['id'] ?>" method="post">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($event['title']) ?>" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="event_date" class="form-control" value="<?= htmlspecialchars($event['event_date']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Time</label>
                            <input type="time" name="event_time" class="form-control" value="<?= htmlspecialchars($event['event_time']) ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($event['location']) ?>" placeholder="e.g., Conference Room">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Church</label>
                        <select name="church_id" class="form-select" required>
                            <?php foreach ($churches as $church): ?>
                                <option value="<?= (int)$church['id'] ?>" <?= (int)$church['id'] === (int)$event['church_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($church['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if (!empty($lifegroups)): ?>
                    <div class="mb-3">
                        <label class="form-label">Lifegroup</label>
                        <select name="lifegroup_id" class="form-select">
                            <option value="">Select Lifegroup</option>
                            <?php foreach ($lifegroups as $lg): ?>
                                <option value="<?= (int)$lg['id'] ?>" <?= !empty($event['lifegroup_id']) && (int)$lg['id'] === (int)$event['lifegroup_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($lg['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Options are filtered based on your role and assignments.</small>
                    </div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Optional"><?= htmlspecialchars($event['description']) ?></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                        <a href="/events/lifegroup" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


