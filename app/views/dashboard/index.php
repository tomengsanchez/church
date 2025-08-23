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
     
     <!-- In Progress Members Row -->
     <div class="row">
         <div class="col-md-3 mb-4">
             <div class="card bg-secondary text-white">
                 <div class="card-body">
                     <div class="d-flex justify-content-between">
                         <div>
                             <h5 class="card-title">In Progress</h5>
                             <h3><?= $stats['in_progress_members'] ?? 0 ?></h3>
                         </div>
                         <div class="align-self-center">
                             <i class="fas fa-clock fa-2x"></i>
                         </div>
                     </div>
                 </div>
             </div>
         </div>
     </div>

    <!-- Member Status Percentages -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>Member Status Distribution
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($status_percentages as $status): ?>
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="badge bg-<?= $status['badge_class'] ?? 'secondary' ?> fs-6">
                                        <?= $status['count'] ?>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0"><?= htmlspecialchars($status['status_name']) ?></h6>
                                    <small class="text-muted"><?= $status['count'] ?> members (<?= $status['percentage'] ?>%)</small>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hierarchy Statistics -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-sitemap me-2"></i>Leadership Hierarchy
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card bg-primary text-white clickable" onclick="showCoachHierarchy()">
                                <div class="card-body text-center">
                                    <i class="fas fa-user-tie fa-3x mb-3"></i>
                                    <h3><?= $hierarchy_stats['coaches'] ?? 0 ?></h3>
                                    <h5>Coaches</h5>
                                    <small>Click to view details</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-success text-white clickable" onclick="showMentorHierarchy()">
                                <div class="card-body text-center">
                                    <i class="fas fa-user-graduate fa-3x mb-3"></i>
                                    <h3><?= $hierarchy_stats['mentors'] ?? 0 ?></h3>
                                    <h5>Mentors</h5>
                                    <small>Click to view details</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-info text-white clickable" onclick="showLifegroupsHierarchy()">
                                <div class="card-body text-center">
                                    <i class="fas fa-users fa-3x mb-3"></i>
                                    <h3><?= $hierarchy_stats['lifegroups'] ?? 0 ?></h3>
                                    <h5>Lifegroups</h5>
                                    <small>Click to view details</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hierarchy Details Modal -->
    <div class="modal fade" id="hierarchyModal" tabindex="-1" aria-labelledby="hierarchyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="hierarchyModalLabel">Hierarchy Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="hierarchyModalBody">
                    <!-- Content will be loaded here -->
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

<?php if ($user['role'] === ROLE_PASTOR): ?>
<style>
.clickable {
    cursor: pointer;
    transition: transform 0.2s ease-in-out;
}

.clickable:hover {
    transform: scale(1.05);
}

.hierarchy-item {
    border-left: 3px solid #007bff;
    padding-left: 15px;
    margin-bottom: 10px;
}

.hierarchy-item h6 {
    margin-bottom: 5px;
}

.hierarchy-item .badge {
    font-size: 0.8em;
}
</style>

<script>
function showCoachHierarchy() {
    const modalBody = document.getElementById('hierarchyModalBody');
    modalBody.innerHTML = `
        <div class="text-center">
            <h5>Coaches Overview</h5>
            <p>Total Coaches: <span class="badge bg-primary"><?= $hierarchy_stats['coaches'] ?? 0 ?></span></p>
            <p>Total Mentors: <span class="badge bg-success"><?= $hierarchy_stats['mentors'] ?? 0 ?></span></p>
            <p>Total Lifegroups: <span class="badge bg-info"><?= $hierarchy_stats['lifegroups'] ?? 0 ?></span></p>
            <hr>
            <p class="text-muted">This shows the overall hierarchy structure. For detailed breakdown by individual coaches, you would need to implement a coaches list view.</p>
        </div>
    `;
    
    document.getElementById('hierarchyModalLabel').textContent = 'Coaches Hierarchy Overview';
    new bootstrap.Modal(document.getElementById('hierarchyModal')).show();
}

function showMentorHierarchy() {
    const modalBody = document.getElementById('hierarchyModalBody');
    modalBody.innerHTML = `
        <div class="text-center">
            <h5>Mentors Overview</h5>
            <p>Total Mentors: <span class="badge bg-success"><?= $hierarchy_stats['mentors'] ?? 0 ?></span></p>
            <p>Total Lifegroups: <span class="badge bg-info"><?= $hierarchy_stats['lifegroups'] ?? 0 ?></span></p>
            <hr>
            <p class="text-muted">This shows the overall mentors structure. For detailed breakdown by individual mentors, you would need to implement a mentors list view.</p>
        </div>
    `;
    
    document.getElementById('hierarchyModalLabel').textContent = 'Mentors Hierarchy Overview';
    new bootstrap.Modal(document.getElementById('hierarchyModal')).show();
}

function showLifegroupsHierarchy() {
    const modalBody = document.getElementById('hierarchyModalBody');
    modalBody.innerHTML = `
        <div class="text-center">
            <h5>Lifegroups Overview</h5>
            <p>Total Lifegroups: <span class="badge bg-info"><?= $hierarchy_stats['lifegroups'] ?? 0 ?></span></p>
            <hr>
            <p class="text-muted">Loading lifegroups details...</p>
        </div>
    `;
    
    document.getElementById('hierarchyModalLabel').textContent = 'Lifegroups Hierarchy';
    new bootstrap.Modal(document.getElementById('hierarchyModal')).show();
    
    // Load lifegroups details via AJAX
    getLifegroupsHierarchyDetails();
}

// Function to get coach hierarchy details via AJAX
function getCoachHierarchyDetails(coachId) {
    fetch('/dashboard/getCoachHierarchy', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `coach_id=${coachId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert('Error: ' + data.error);
            return;
        }
        
        let html = '<h5>Coach Hierarchy Details</h5>';
        
        if (data.mentors && data.mentors.length > 0) {
            html += '<h6>Mentors under this coach:</h6>';
            data.mentors.forEach(mentor => {
                html += `
                    <div class="hierarchy-item">
                        <h6><i class="fas fa-user-graduate me-2"></i>${mentor.name}</h6>
                        <p>Members: <span class="badge bg-success">${mentor.member_count}</span></p>
                    </div>
                `;
            });
        }
        
        if (data.lifegroups && data.lifegroups.length > 0) {
            html += '<h6>Lifegroups under this coach:</h6>';
            data.lifegroups.forEach(lifegroup => {
                html += `
                    <div class="hierarchy-item">
                        <h6><i class="fas fa-users me-2"></i>${lifegroup.name}</h6>
                        <p>Members: <span class="badge bg-info">${lifegroup.member_count}</span></p>
                    </div>
                `;
            });
        }
        
        document.getElementById('hierarchyModalBody').innerHTML = html;
        document.getElementById('hierarchyModalLabel').textContent = 'Coach Hierarchy Details';
        new bootstrap.Modal(document.getElementById('hierarchyModal')).show();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading hierarchy details');
    });
}

// Function to get mentor hierarchy details via AJAX
function getMentorHierarchyDetails(mentorId) {
    fetch('/dashboard/getMentorHierarchy', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `mentor_id=${mentorId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert('Error: ' + data.error);
            return;
        }
        
        let html = '<h5>Mentor Hierarchy Details</h5>';
        
        if (data.lifegroups && data.lifegroups.length > 0) {
            html += '<h6>Lifegroups under this mentor:</h6>';
            data.lifegroups.forEach(lifegroup => {
                html += `
                    <div class="hierarchy-item">
                        <h6><i class="fas fa-users me-2"></i>${lifegroup.name}</h6>
                        <p>Members: <span class="badge bg-info">${lifegroup.member_count}</span></p>
                    </div>
                `;
            });
        } else {
            html += '<p class="text-muted">No lifegroups found under this mentor.</p>';
        }
        
        document.getElementById('hierarchyModalBody').innerHTML = html;
        document.getElementById('hierarchyModalLabel').textContent = 'Mentor Hierarchy Details';
        new bootstrap.Modal(document.getElementById('hierarchyModal')).show();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading hierarchy details');
    });
}

// Function to get lifegroups hierarchy details via AJAX
function getLifegroupsHierarchyDetails() {
    fetch('/dashboard/getLifegroupsHierarchy', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `church_id=<?= $user['church_id'] ?? 1 ?>`
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert('Error: ' + data.error);
            return;
        }
        
        let html = '<h5>Lifegroups Details</h5>';
        
        if (data && data.length > 0) {
            html += `
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th><i class="fas fa-users me-2"></i>Lifegroup Name</th>
                                <th><i class="fas fa-user-friends me-2"></i>Members</th>
                                <th><i class="fas fa-user-graduate me-2"></i>Mentors</th>
                                <th><i class="fas fa-user-tie me-2"></i>Coaches</th>
                                <th><i class="fas fa-info-circle me-2"></i>Description</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            data.forEach(lifegroup => {
                html += `
                    <tr>
                        <td><strong>${lifegroup.name}</strong></td>
                        <td><span class="badge bg-info">${lifegroup.member_count || 0}</span></td>
                        <td><span class="text-success">${lifegroup.mentor_names || 'N/A'}</span></td>
                        <td><span class="text-primary">${lifegroup.coach_names || 'N/A'}</span></td>
                        <td><small class="text-muted">${lifegroup.description || 'No description'}</small></td>
                    </tr>
                `;
            });
            
            html += `
                        </tbody>
                    </table>
                </div>
            `;
        } else {
            html += '<p class="text-muted">No lifegroups found.</p>';
        }
        
        document.getElementById('hierarchyModalBody').innerHTML = html;
        document.getElementById('hierarchyModalLabel').textContent = 'Lifegroups Hierarchy Details';
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading lifegroups details');
    });
}
</script>
<?php endif; ?> 