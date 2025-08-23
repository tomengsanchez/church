<?php $layout = 'layouts/authenticated'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">
                <i class="fas fa-user-plus me-2"></i>New Friends
            </h1>
            <div>
                <a href="/friend/create" class="btn btn-warning">
                    <i class="fas fa-plus me-2"></i>Add New Friend
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0"><?= $stats['total'] ?? 0 ?></h4>
                        <p class="mb-0">Total Friends</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0"><?= $stats['assigned'] ?? 0 ?></h4>
                        <p class="mb-0">Assigned</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0"><?= $stats['unassigned'] ?? 0 ?></h4>
                        <p class="mb-0">Unassigned</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="/friend" class="row g-3">
                    <div class="col-md-3">
                        <label for="status_filter" class="form-label">Status</label>
                        <select class="form-select" id="status_filter" name="status">
                            <option value="">All Status</option>
                            <option value="pending" <?= ($_GET['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="church_filter" class="form-label">Church</label>
                        <select class="form-select" id="church_filter" name="church_id">
                            <option value="">All Churches</option>
                            <?php foreach ($churches as $church): ?>
                            <option value="<?= $church['id'] ?>" <?= ($_GET['church_id'] ?? '') == $church['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($church['name'] ?? '') ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" placeholder="Name or Email">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-outline-primary me-2">
                            <i class="fas fa-search me-1"></i>Filter
                        </button>
                        <a href="/friend" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Clear
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Friends Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <?php if (!empty($friends)): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Contact</th>
                                <th>Church</th>
                                <th>Pastor</th>
                                <th>Coach</th>
                                <th>Mentor</th>
                                <th>Lifegroup</th>
                                <th>Status</th>
                                <th>Added</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($friends as $friend): ?>
                            <tr class="table-warning">
                                <td>
                                    <strong><?= htmlspecialchars($friend['name'] ?? '') ?></strong>
                                    <span class="badge bg-warning text-dark ms-2">New Friend</span>
                                </td>
                                <td>
                                    <?php 
                                    $email = $friend['email'] ?? '';
                                    $phone = $friend['phone'] ?? '';
                                    if (strpos($email, 'no-email-') === 0 && strpos($email, '@placeholder.local') !== false) {
                                        echo '<span class="text-muted">No Email</span>';
                                    } else {
                                        echo htmlspecialchars($email);
                                    }
                                    if (!empty($phone)) {
                                        echo '<br><small class="text-muted">' . htmlspecialchars($phone) . '</small>';
                                    }
                                    ?>
                                </td>
                                <td><?= htmlspecialchars($friend['church_name'] ?? 'Not Assigned') ?></td>
                                <td><?= htmlspecialchars($friend['pastor_name'] ?? 'Not Assigned') ?></td>
                                <td><?= htmlspecialchars($friend['coach_name'] ?? 'Not Assigned') ?></td>
                                <td><?= htmlspecialchars($friend['mentor_name'] ?? 'Not Assigned') ?></td>
                                <td><?= htmlspecialchars($friend['lifegroup_name'] ?? 'Not Assigned') ?></td>
                                <td>
                                    <span class="badge bg-warning text-dark">
                                        <?= ucfirst($friend['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('M j, Y', strtotime($friend['created_at'])) ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="/friend/edit/<?= $friend['id'] ?>" class="btn btn-outline-warning btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-success btn-sm" 
                                                onclick="promoteFriend(<?= $friend['id'] ?>, '<?= htmlspecialchars($friend['name'] ?? '') ?>')" title="Promote to Member">
                                            <i class="fas fa-arrow-up"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm" 
                                                onclick="deleteFriend(<?= $friend['id'] ?>, '<?= htmlspecialchars($friend['name'] ?? '') ?>')" title="Delete">
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
                    <i class="fas fa-user-plus fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No New Friends Found</h4>
                    <p class="text-muted">Start by adding your first new friend.</p>
                    <a href="/friend/create" class="btn btn-warning">
                        <i class="fas fa-plus me-2"></i>Add New Friend
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function deleteFriend(id, name) {
    if (confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/friend/delete/${id}`;
        document.body.appendChild(form);
        form.submit();
    }
}

function promoteFriend(id, name) {
    if (confirm(`Are you sure you want to promote "${name}" to active member? You will need to assign Church, Pastor, and Coach.`)) {
        window.location.href = `/friend/promote/${id}`;
    }
}
</script>
