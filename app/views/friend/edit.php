<?php $layout = 'layouts/authenticated'; ?>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h4 class="mb-0">
                    <i class="fas fa-edit me-2"></i>Edit Friend: <?= htmlspecialchars($friend['name'] ?? '') ?>
                </h4>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Pending Friend:</strong> This friend is currently in "Pending" status. Complete their profile by assigning them to a church, coach, mentor, and lifegroup. You can also promote them to an active member when ready.
                </div>
                
                <form method="POST" action="/friend/edit/<?= $friend['id'] ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?= htmlspecialchars($friend['name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($friend['email'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?= htmlspecialchars($friend['phone'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="2"><?= htmlspecialchars($friend['address'] ?? '') ?></textarea>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="church_id" class="form-label">Church</label>
                            <select class="form-select" id="church_id" name="church_id">
                                <option value="">Select Church</option>
                                <?php foreach ($churches as $church): ?>
                                <option value="<?= $church['id'] ?>" <?= ($friend['church_id'] ?? '') == $church['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($church['name'] ?? '') ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <?php foreach ($memberStatuses as $status): ?>
                                <option value="<?= $status['slug'] ?>" <?= ($friend['status'] ?? '') === $status['slug'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($status['name'] ?? '') ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="coach_id" class="form-label">Coach</label>
                            <select class="form-select" id="coach_id" name="coach_id">
                                <option value="">Select Coach</option>
                                <?php foreach ($coaches as $coach): ?>
                                <option value="<?= $coach['id'] ?>" <?= ($currentCoach['id'] ?? '') == $coach['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($coach['name'] ?? '') ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="mentor_id" class="form-label">Mentor</label>
                            <select class="form-select" id="mentor_id" name="mentor_id">
                                <option value="">Select Mentor</option>
                                <?php foreach ($mentors as $mentor): ?>
                                <option value="<?= $mentor['id'] ?>" <?= ($currentMentor['id'] ?? '') == $mentor['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($mentor['name'] ?? '') ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="lifegroup_id" class="form-label">Lifegroup</label>
                            <select class="form-select" id="lifegroup_id" name="lifegroup_id">
                                <option value="">Select Lifegroup</option>
                                <?php foreach ($lifegroups as $lifegroup): ?>
                                <option value="<?= $lifegroup['id'] ?>" <?= ($currentLifegroup['id'] ?? '') == $lifegroup['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($lifegroup['name'] ?? '') ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Leave blank to keep current password">
                            <div class="form-text">Only fill this if you want to change the password</div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="/friend" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Friends
                        </a>
                        <div>
                            <button type="submit" class="btn btn-warning me-2">
                                <i class="fas fa-save me-2"></i>Update Friend
                            </button>
                            <button type="button" class="btn btn-success" onclick="promoteFriend(<?= $friend['id'] ?>, '<?= htmlspecialchars($friend['name'] ?? '') ?>')">
                                <i class="fas fa-arrow-up me-2"></i>Promote to Member
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function promoteFriend(id, name) {
    if (confirm(`Are you sure you want to promote "${name}" to active member? They will be moved to the regular members list.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/friend/promote/${id}`;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
