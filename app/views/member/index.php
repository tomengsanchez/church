<?php $layout = 'layouts/authenticated'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">
                <i class="fas fa-users me-2"></i>Members
            </h1>
            <div>
                <a href="/member/create" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add Member
                </a>
            </div>
        </div>
    </div>
</div>



<!-- Filter Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="/member" class="row g-3">
                    <div class="col-md-3">
                        <label for="status_filter" class="form-label">Status</label>
                        <select class="form-select" id="status_filter" name="status">
                            <option value="">All Status</option>
                            <?php if (!empty($memberStatuses ?? [])): ?>
                                <?php foreach ($memberStatuses as $status): ?>
                                    <?php if ((int)$status['is_active'] === 1): ?>
                                        <option value="<?= htmlspecialchars($status['slug']) ?>" <?= ($_GET['status'] ?? '') === $status['slug'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($status['name']) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
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
                        <a href="/member" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Clear
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Members Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <!-- Pagination Controls -->
                <div class="pagination-controls">
                    <?= $pagination->renderSearchAndControls() ?>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th data-sort="u.name" style="cursor: pointer;">
                                    Name
                                    <i class="fas fa-sort"></i>
                                </th>
                                <th data-sort="u.email" style="cursor: pointer;">
                                    Email
                                    <i class="fas fa-sort"></i>
                                </th>
                                <th data-sort="ch.name" style="cursor: pointer;">
                                    Church
                                    <i class="fas fa-sort"></i>
                                </th>
                                <th data-sort="p.name" style="cursor: pointer;">
                                    Pastor
                                    <i class="fas fa-sort"></i>
                                </th>
                                <th data-sort="c.name" style="cursor: pointer;">
                                    Coach
                                    <i class="fas fa-sort"></i>
                                </th>
                                <th data-sort="m.name" style="cursor: pointer;">
                                    Mentor
                                    <i class="fas fa-sort"></i>
                                </th>
                                <th data-sort="l.name" style="cursor: pointer;">
                                    Lifegroup
                                    <i class="fas fa-sort"></i>
                                </th>
                                <th data-sort="u.status" style="cursor: pointer;">
                                    Status
                                    <i class="fas fa-sort"></i>
                                </th>
                                <th data-sort="u.created_at" style="cursor: pointer;">
                                    Joined
                                    <i class="fas fa-sort"></i>
                                </th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($members)): ?>
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <i class="fas fa-users fa-2x text-muted mb-2 d-block"></i>
                                        <h6 class="text-muted">No Members Found</h6>
                                        <p class="text-muted mb-0">No members match your current search criteria.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($members as $member): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($member['name'] ?? '') ?></strong>
                                    </td>
                                    <td>
                                        <?php 
                                        $email = $member['email'] ?? '';
                                        if (strpos($email, 'no-email-') === 0 && strpos($email, '@placeholder.local') !== false) {
                                            echo '<span class="text-muted">No Email</span>';
                                        } else {
                                            echo htmlspecialchars($email);
                                        }
                                        ?>
                                    </td>
                                    <td><?= htmlspecialchars($member['church_name'] ?? 'Not Assigned') ?></td>
                                    <td><?= htmlspecialchars($member['pastor_name'] ?? 'Not Assigned') ?></td>
                                    <td><?= htmlspecialchars($member['coach_name'] ?? 'Not Assigned') ?></td>
                                    <td><?= htmlspecialchars($member['mentor_name'] ?? 'Not Assigned') ?></td>
                                    <td><?= htmlspecialchars($member['lifegroup_name'] ?? 'Not Assigned') ?></td>
                                    <td>
                                        <span class="badge bg-<?= getStatusBadgeClass($member['status']) ?>">
                                            <?= ucfirst($member['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('M j, Y', strtotime($member['created_at'])) ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="/member/edit/<?= $member['id'] ?>" class="btn btn-outline-primary btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                                    onclick="deleteMember(<?= $member['id'] ?>, '<?= htmlspecialchars($member['name'] ?? '') ?>')" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination Controls -->
                <?= $pagination->renderPaginationControls() ?>
            </div>
        </div>
    </div>
</div>

<script>
function deleteMember(id, name) {
    if (confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/member/delete/${id}`;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>