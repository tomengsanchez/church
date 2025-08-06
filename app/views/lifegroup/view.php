<?php $layout = 'layouts/authenticated'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">
                <i class="fas fa-users me-2"></i><?= htmlspecialchars($lifegroup['name'] ?? '') ?>
            </h1>
            <div>
                <?php if (hasPermission(ROLE_SUPER_ADMIN) || ($_SESSION['user_id'] == $lifegroup['mentor_id'])): ?>
                <a href="/lifegroup/edit/<?= $lifegroup['id'] ?>" class="btn btn-secondary">
                    <i class="fas fa-edit me-2"></i>Edit
                </a>
                <?php endif; ?>
                <a href="/lifegroup" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Lifegroups
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Lifegroup Details
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Name:</strong> <?= htmlspecialchars($lifegroup['name'] ?? '') ?></p>
                        <p><strong>Church:</strong> <?= htmlspecialchars($lifegroup['church_name'] ?? 'N/A') ?></p>
                        <p><strong>Mentor:</strong> <?= htmlspecialchars($lifegroup['mentor_name'] ?? 'N/A') ?></p>
                        <p><strong>Status:</strong> 
                            <span class="badge bg-<?= $lifegroup['status'] === 'active' ? 'success' : ($lifegroup['status'] === 'inactive' ? 'secondary' : 'warning') ?>">
                                <?= ucfirst($lifegroup['status']) ?>
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Meeting Day:</strong> <?= htmlspecialchars($lifegroup['meeting_day'] ?? 'Not scheduled') ?></p>
                        <p><strong>Meeting Time:</strong> <?= $lifegroup['meeting_time'] ? date('g:i A', strtotime($lifegroup['meeting_time'])) : 'Not scheduled' ?></p>
                        <p><strong>Location:</strong> <?= htmlspecialchars($lifegroup['meeting_location'] ?? 'Not specified') ?></p>
                        <p><strong>Max Members:</strong> <?= htmlspecialchars($lifegroup['max_members'] ?? '20') ?></p>
                    </div>
                </div>
                
                <?php if (!empty($lifegroup['description'])): ?>
                <div class="mt-3">
                    <h6>Description:</h6>
                    <p class="text-muted"><?= nl2br(htmlspecialchars($lifegroup['description'])) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-users me-2"></i>Members (<?= count($members) ?>/<?= $lifegroup['max_members'] ?? 20 ?>)
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($members)): ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($members as $member): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?= htmlspecialchars($member['name'] ?? '') ?></strong>
                            <br><small class="text-muted"><?= htmlspecialchars($member['email'] ?? '') ?></small>
                        </div>
                        <small class="text-muted">Joined: <?= date('M j, Y', strtotime($member['joined_date'])) ?></small>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-muted text-center">No members yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div> 