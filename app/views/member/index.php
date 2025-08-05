<?php $layout = 'layouts/authenticated'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">
                <i class="fas fa-users me-2"></i>Members
            </h1>
            <a href="/member/create" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add Member
            </a>
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
                            <option value="active" <?= ($_GET['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($_GET['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            <option value="pending" <?= ($_GET['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="suspended" <?= ($_GET['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="church_filter" class="form-label">Church</label>
                        <select class="form-select" id="church_filter" name="church_id">
                            <option value="">All Churches</option>
                            <?php foreach ($churches as $church): ?>
                            <option value="<?= $church['id'] ?>" <?= ($_GET['church_id'] ?? '') == $church['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($church['name']) ?>
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

<div class="row">
    <?php foreach ($members as $member): ?>
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user me-2"></i><?= htmlspecialchars($member['name']) ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Email:</strong><br>
                    <?= htmlspecialchars($member['email']) ?>
                </div>
                <div class="mb-3">
                    <strong>Church:</strong><br>
                    <?= htmlspecialchars($member['church_name'] ?? 'Not Assigned') ?>
                </div>
                <div class="mb-3">
                    <strong>Pastor:</strong><br>
                    <?= htmlspecialchars($member['pastor_name'] ?? 'Not Assigned') ?>
                </div>
                <div class="mb-3">
                    <strong>Coach:</strong><br>
                    <?= htmlspecialchars($member['coach_name'] ?? 'Not Assigned') ?>
                </div>
                <div class="mb-3">
                    <strong>Mentor:</strong><br>
                    <?= htmlspecialchars($member['mentor_name'] ?? 'Not Assigned') ?>
                </div>
                <div class="mb-3">
                    <strong>Status:</strong>
                    <span class="badge bg-<?= getStatusBadgeClass($member['status']) ?>">
                        <?= ucfirst($member['status']) ?>
                    </span>
                </div>
                <div class="mb-3">
                    <strong>Joined:</strong> <?= date('M j, Y', strtotime($member['created_at'])) ?>
                </div>
            </div>
            <div class="card-footer">
                <div class="btn-group w-100" role="group">
                    <a href="/member/edit/<?= $member['id'] ?>" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                    <button type="button" class="btn btn-outline-danger btn-sm" 
                            onclick="deleteMember(<?= $member['id'] ?>, '<?= htmlspecialchars($member['name']) ?>')">
                        <i class="fas fa-trash me-1"></i>Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (empty($members)): ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">No Members Found</h4>
                <p class="text-muted">Start by adding your first member.</p>
                <a href="/member/create" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add Member
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Pagination -->
<?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
<nav aria-label="Member pagination">
    <ul class="pagination justify-content-center">
        <?php if ($pagination['current_page'] > 1): ?>
        <li class="page-item">
            <a class="page-link" href="?page=<?= $pagination['current_page'] - 1 ?>&status=<?= $_GET['status'] ?? '' ?>&church_id=<?= $_GET['church_id'] ?? '' ?>&search=<?= $_GET['search'] ?? '' ?>">Previous</a>
        </li>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
        <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $i ?>&status=<?= $_GET['status'] ?? '' ?>&church_id=<?= $_GET['church_id'] ?? '' ?>&search=<?= $_GET['search'] ?? '' ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
        
        <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
        <li class="page-item">
            <a class="page-link" href="?page=<?= $pagination['current_page'] + 1 ?>&status=<?= $_GET['status'] ?? '' ?>&church_id=<?= $_GET['church_id'] ?? '' ?>&search=<?= $_GET['search'] ?? '' ?>">Next</a>
        </li>
        <?php endif; ?>
    </ul>
</nav>
<?php endif; ?>

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

function getStatusBadgeClass(status) {
    switch (status) {
        case 'active': return 'success';
        case 'inactive': return 'secondary';
        case 'pending': return 'warning';
        case 'suspended': return 'danger';
        default: return 'secondary';
    }
}
</script> 