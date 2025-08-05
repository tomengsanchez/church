<?php $layout = 'layouts/authenticated'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">
                <i class="fas fa-user-graduate me-2"></i>Mentors
            </h1>
            <a href="/mentor/create" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add Mentor
            </a>
        </div>
    </div>
</div>

<div class="row">
    <?php foreach ($mentors as $mentor): ?>
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user-graduate me-2"></i><?= htmlspecialchars($mentor['name']) ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Email:</strong><br>
                    <?= htmlspecialchars($mentor['email']) ?>
                </div>
                <div class="mb-3">
                    <strong>Church:</strong><br>
                    <?= htmlspecialchars($mentor['church_name'] ?? 'Not Assigned') ?>
                </div>
                <div class="mb-3">
                    <strong>Pastor:</strong><br>
                    <?= htmlspecialchars($mentor['pastor_name'] ?? 'Not Assigned') ?>
                </div>
                <div class="mb-3">
                    <strong>Coach:</strong><br>
                    <?= htmlspecialchars($mentor['coach_name'] ?? 'Not Assigned') ?>
                </div>
                <div class="mb-3">
                    <strong>Status:</strong>
                    <span class="badge bg-<?= $mentor['status'] === 'active' ? 'success' : 'secondary' ?>">
                        <?= ucfirst($mentor['status']) ?>
                    </span>
                </div>
                <div class="mb-3">
                    <strong>Members:</strong> <?= $mentor['member_count'] ?? 0 ?><br>
                    <strong>Active Members:</strong> <?= $mentor['active_member_count'] ?? 0 ?>
                </div>
            </div>
            <div class="card-footer">
                <div class="btn-group w-100" role="group">
                    <a href="/mentor/edit/<?= $mentor['id'] ?>" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                    <button type="button" class="btn btn-outline-danger btn-sm" 
                            onclick="deleteMentor(<?= $mentor['id'] ?>, '<?= htmlspecialchars($mentor['name']) ?>')">
                        <i class="fas fa-trash me-1"></i>Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (empty($mentors)): ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-user-graduate fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">No Mentors Found</h4>
                <p class="text-muted">Start by adding your first mentor.</p>
                <a href="/mentor/create" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add Mentor
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function deleteMentor(id, name) {
    if (confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/mentor/delete/${id}`;
        document.body.appendChild(form);
        form.submit();
    }
}
</script> 