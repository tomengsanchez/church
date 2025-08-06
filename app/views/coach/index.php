<?php $layout = 'layouts/authenticated'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">
                <i class="fas fa-user-graduate me-2"></i>Coaches
            </h1>
            <a href="/coach/create" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add Coach
            </a>
        </div>
    </div>
</div>

<!-- Coaches Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <?php if (!empty($coaches)): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Satelife Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Church</th>
                                <th>Pastor</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($coaches as $coach): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($coach['name'] ?? '') ?></strong>
                                </td>
                                <td>
                                    <?php if (!empty($coach['satelife_name'])): ?>
                                        <span class="badge bg-info"><?= htmlspecialchars($coach['satelife_name']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($coach['email'] ?? '') ?></td>
                                <td><?= htmlspecialchars($coach['phone'] ?? '') ?></td>
                                <td><?= htmlspecialchars($coach['church_name'] ?? 'Not Assigned') ?></td>
                                <td><?= htmlspecialchars($coach['pastor_name'] ?? 'Not Assigned') ?></td>
                                <td>
                                    <span class="badge bg-<?= getStatusBadgeClass($coach['status']) ?>">
                                        <?= ucfirst($coach['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('M j, Y', strtotime($coach['created_at'])) ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="/coach/edit/<?= $coach['id'] ?>" class="btn btn-outline-primary btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger btn-sm" 
                                                onclick="deleteCoach(<?= $coach['id'] ?>, '<?= htmlspecialchars($coach['name'] ?? '') ?>')" title="Delete">
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
                <nav aria-label="Coach pagination" class="mt-4">
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
                    <i class="fas fa-user-graduate fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No Coaches Found</h4>
                    <p class="text-muted">Start by adding your first coach.</p>
                    <a href="/coach/create" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add Coach
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function deleteCoach(id, name) {
    if (confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/coach/delete/${id}`;
        document.body.appendChild(form);
        form.submit();
    }
}
</script> 