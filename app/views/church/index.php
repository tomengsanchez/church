<?php $layout = 'layouts/authenticated'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">
                <i class="fas fa-church me-2"></i>Churches
            </h1>
            <a href="/church/create" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add Church
            </a>
        </div>
    </div>
</div>

<div class="row">
    <?php foreach ($churches as $church): ?>
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-church me-2"></i><?= htmlspecialchars($church['name']) ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Address:</strong><br>
                    <?= htmlspecialchars($church['address']) ?>
                </div>
                <div class="mb-3">
                    <strong>Phone:</strong><br>
                    <?= htmlspecialchars($church['phone']) ?>
                </div>
                <div class="mb-3">
                    <strong>Email:</strong><br>
                    <?= htmlspecialchars($church['email']) ?>
                </div>
                <div class="mb-3">
                    <strong>Pastors:</strong> <?= $church['pastor_count'] ?? 0 ?><br>
                    <strong>Members:</strong> <?= $church['member_count'] ?? 0 ?>
                </div>
            </div>
            <div class="card-footer">
                <div class="btn-group w-100" role="group">
                    <a href="/church/edit/<?= $church['id'] ?>" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                    <button type="button" class="btn btn-outline-danger btn-sm" 
                            onclick="deleteChurch(<?= $church['id'] ?>, '<?= htmlspecialchars($church['name']) ?>')">
                        <i class="fas fa-trash me-1"></i>Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (empty($churches)): ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-church fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">No Churches Found</h4>
                <p class="text-muted">Start by adding your first church.</p>
                <a href="/church/create" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add Church
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function deleteChurch(id, name) {
    if (confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/church/delete/${id}`;
        document.body.appendChild(form);
        form.submit();
    }
}
</script> 