<?php $layout = 'layouts/authenticated'; ?>

<div class="row">
    <div class="col-12">
        <h1 class="mb-4">
            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
        </h1>
    </div>
</div>

<?php if ($user['role'] === ROLE_SUPER_ADMIN): ?>
    <!-- Super Admin Dashboard -->
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Churches</h5>
                            <h3><?= count($churches) ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-church fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Total Members</h5>
                            <h3><?= $stats['total_members'] ?? 0 ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Active Members</h5>
                            <h3><?= $stats['active_members'] ?? 0 ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-check fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Inactive Members</h5>
                            <h3><?= $stats['inactive_members'] ?? 0 ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-times fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-church me-2"></i>Churches Overview
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Church Name</th>
                                    <th>Pastor</th>
                                    <th>Coaches</th>
                                    <th>Mentors</th>
                                    <th>Members</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($churches as $church): ?>
                                <tr>
                                    <td><?= htmlspecialchars($church['name']) ?></td>
                                    <td><?= htmlspecialchars($church['pastor_name'] ?? 'N/A') ?></td>
                                    <td><?= $church['coach_count'] ?></td>
                                    <td><?= $church['mentor_count'] ?></td>
                                    <td><?= $church['member_count'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php elseif ($user['role'] === ROLE_PASTOR): ?>
    <!-- Pastor Dashboard -->
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Church</h5>
                            <h3><?= htmlspecialchars($church['name']) ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-church fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Total Members</h5>
                            <h3><?= $stats['total_members'] ?? 0 ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Active Members</h5>
                            <h3><?= $stats['active_members'] ?? 0 ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-check fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Inactive Members</h5>
                            <h3><?= $stats['inactive_members'] ?? 0 ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-times fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php elseif ($user['role'] === ROLE_COACH): ?>
    <!-- Coach Dashboard -->
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Total Members</h5>
                            <h3><?= $stats['total_members'] ?? 0 ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Active Members</h5>
                            <h3><?= $stats['active_members'] ?? 0 ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-check fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Inactive Members</h5>
                            <h3><?= $stats['inactive_members'] ?? 0 ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-times fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php elseif ($user['role'] === ROLE_MENTOR): ?>
    <!-- Mentor Dashboard -->
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Total Members</h5>
                            <h3><?= $stats['total_members'] ?? 0 ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Active Members</h5>
                            <h3><?= $stats['active_members'] ?? 0 ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-check fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Inactive Members</h5>
                            <h3><?= $stats['inactive_members'] ?? 0 ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-times fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- Member Dashboard -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>My Profile
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (isset($profile)): ?>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> <?= htmlspecialchars($profile['name']) ?></p>
                            <p><strong>Email:</strong> <?= htmlspecialchars($profile['email']) ?></p>
                            <p><strong>Church:</strong> <?= htmlspecialchars($profile['church_name'] ?? 'N/A') ?></p>
                            <p><strong>Status:</strong> 
                                <span class="badge bg-<?= $profile['status'] === 'active' ? 'success' : 'warning' ?>">
                                    <?= ucfirst($profile['status']) ?>
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Pastor:</strong> <?= htmlspecialchars($profile['pastor_name'] ?? 'N/A') ?></p>
                            <p><strong>Coach:</strong> <?= htmlspecialchars($profile['coach_name'] ?? 'N/A') ?></p>
                            <p><strong>Mentor:</strong> <?= htmlspecialchars($profile['mentor_name'] ?? 'N/A') ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?> 