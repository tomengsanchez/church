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

<!-- Churches Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <?php if (!empty($churches)): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Address</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Pastor</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($churches as $church): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($church['name'] ?? '') ?></strong>
                                </td>
                                <td><?= htmlspecialchars($church['address'] ?? '') ?></td>
                                <td><?= htmlspecialchars($church['phone'] ?? '') ?></td>
                                <td><?= htmlspecialchars($church['email'] ?? '') ?></td>
                                <td><?= htmlspecialchars($church['pastor_name'] ?? 'Not Assigned') ?></td>
                                <td><?= date('M j, Y', strtotime($church['created_at'])) ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="/church/edit/<?= $church['id'] ?>" class="btn btn-outline-primary btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger btn-sm" 
                                                onclick="deleteChurch(<?= $church['id'] ?>, '<?= htmlspecialchars($church['name'] ?? '') ?>')" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                <nav aria-label="Church pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($pagination['current_page'] > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $pagination['current_page'] - 1 ?>">Previous</a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                        <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $pagination['current_page'] + 1 ?>">Next</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
                
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-church fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No Churches Found</h4>
                    <p class="text-muted">Start by adding your first church.</p>
                    <a href="/church/create" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add Church
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

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