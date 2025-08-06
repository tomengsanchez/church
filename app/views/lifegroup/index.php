<?php $layout = 'layouts/authenticated'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">
                <i class="fas fa-users me-2"></i>Lifegroups
            </h1>
            <?php if (hasPermission(ROLE_MENTOR)): ?>
            <a href="/lifegroup/create" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add Lifegroup
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <?php if (!empty($lifegroups)): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Church</th>
                                <th>Mentor</th>
                                <th>Meeting Info</th>
                                <th>Members</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lifegroups as $lifegroup): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($lifegroup['name'] ?? '') ?></strong>
                                    <?php if (!empty($lifegroup['description'])): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars(substr($lifegroup['description'], 0, 100)) ?><?= strlen($lifegroup['description']) > 100 ? '...' : '' ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($lifegroup['church_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($lifegroup['mentor_name'] ?? 'N/A') ?></td>
                                <td>
                                    <?php if (!empty($lifegroup['meeting_day']) || !empty($lifegroup['meeting_time'])): ?>
                                        <strong><?= htmlspecialchars($lifegroup['meeting_day'] ?? 'TBD') ?></strong><br>
                                        <small class="text-muted">
                                            <?= $lifegroup['meeting_time'] ? date('g:i A', strtotime($lifegroup['meeting_time'])) : 'TBD' ?>
                                            <?php if (!empty($lifegroup['meeting_location'])): ?>
                                                at <?= htmlspecialchars($lifegroup['meeting_location']) ?>
                                            <?php endif; ?>
                                        </small>
                                    <?php else: ?>
                                        <span class="text-muted">Not scheduled</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        <?= $lifegroup['member_count'] ?? 0 ?> / <?= $lifegroup['max_members'] ?? 20 ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $lifegroup['status'] === 'active' ? 'success' : ($lifegroup['status'] === 'inactive' ? 'secondary' : 'warning') ?>">
                                        <?= ucfirst($lifegroup['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="/lifegroup/view/<?= $lifegroup['id'] ?>" class="btn btn-outline-primary btn-sm" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if (hasPermission(ROLE_SUPER_ADMIN) || ($_SESSION['user_id'] == $lifegroup['mentor_id'])): ?>
                                        <a href="/lifegroup/edit/<?= $lifegroup['id'] ?>" class="btn btn-outline-secondary btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteLifegroup(<?= $lifegroup['id'] ?>, '<?= htmlspecialchars($lifegroup['name'] ?? '') ?>')" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
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
                    <h4 class="text-muted">No Lifegroups Found</h4>
                    <p class="text-muted">Start by adding your first lifegroup.</p>
                    <?php if (hasPermission(ROLE_MENTOR)): ?>
                    <a href="/lifegroup/create" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add Lifegroup
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function deleteLifegroup(id, name) {
    if (confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
        window.location.href = `/lifegroup/delete/${id}`;
    }
}
</script> 