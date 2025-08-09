<?php $layout = 'layouts/authenticated'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fas fa-edit me-2"></i>Edit Mentor
                </h4>
            </div>
            <div class="card-body">
                <form method="POST" action="/mentor/edit/<?= $mentor['id'] ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?= htmlspecialchars($mentor['name'] ?? '') ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($mentor['email'] ?? '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?= htmlspecialchars($mentor['phone'] ?? '') ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="church_id" class="form-label">Church *</label>
                            <?php if (isset($userRole) && $userRole === 'coach'): ?>
                                <!-- For coaches, church is pre-selected and non-editable -->
                                <input type="hidden" name="church_id" value="<?= $currentChurchId ?>">
                                <input type="text" class="form-control" value="<?= htmlspecialchars($churches[0]['name'] ?? '') ?>" readonly>
                            <?php else: ?>
                                <!-- For others, church is selectable -->
                                <select class="form-select" id="church_id" name="church_id" required>
                                    <option value="">Select Church</option>
                                    <?php foreach ($churches as $church): ?>
                                    <option value="<?= $church['id'] ?>" <?= $mentor['church_id'] == $church['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($church['name'] ?? '') ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="coach_id" class="form-label">Coach</label>
                            <?php if (isset($userRole) && $userRole === 'coach'): ?>
                                <!-- For coaches, coach is pre-selected and non-editable -->
                                <input type="hidden" name="coach_id" value="<?= $currentCoachId ?>">
                                <input type="text" class="form-control" value="<?= htmlspecialchars($coaches[0]['name'] ?? '') ?>" readonly>
                            <?php else: ?>
                                <!-- For others, coach is selectable -->
                                <select class="form-select" id="coach_id" name="coach_id">
                                    <option value="">Select Coach</option>
                                    <?php if (isset($coaches) && !empty($coaches)): ?>
                                        <?php foreach ($coaches as $coach): ?>
                                        <option value="<?= $coach['id'] ?>" <?= ($currentCoach && $currentCoach['id'] == $coach['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($coach['name'] ?? '') ?>
                                        </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            <?php endif; ?>
                            <div class="form-text">
                                <?php if (isset($userRole) && $userRole === 'coach'): ?>
                                    This mentor will remain assigned to you.
                                <?php else: ?>
                                    Optional: Assign a coach to mentor this mentor. Select a church first to see available coaches.
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="password" name="password">
                            <div class="form-text">Leave blank to keep current password.</div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?= htmlspecialchars($mentor['address'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="/mentor" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Mentors
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Mentor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    
    if (password && confirmPassword && password !== confirmPassword) {
        this.setCustomValidity('Passwords do not match');
    } else {
        this.setCustomValidity('');
    }
});

<?php if (!isset($userRole) || $userRole !== 'coach'): ?>
// Filter coaches based on selected church (only for non-coaches)
document.getElementById('church_id').addEventListener('change', function() {
    const churchId = this.value;
    const coachSelect = document.getElementById('coach_id');
    const currentCoachId = '<?= $currentCoach ? $currentCoach['id'] : '' ?>';
    
    // Reset coach selection
    coachSelect.innerHTML = '<option value="">Select Coach</option>';
    
    if (churchId) {
        // Fetch coaches for the selected church
        fetch(`/mentor/coaches/${churchId}`)
            .then(response => response.json())
            .then(coaches => {
                coaches.forEach(coach => {
                    const option = document.createElement('option');
                    option.value = coach.id;
                    option.textContent = coach.name;
                    // Select current coach if it matches
                    if (coach.id == currentCoachId) {
                        option.selected = true;
                    }
                    coachSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error fetching coaches:', error);
            });
    }
});

// Load coaches on page load if church is already selected
document.addEventListener('DOMContentLoaded', function() {
    const churchSelect = document.getElementById('church_id');
    if (churchSelect.value) {
        // Trigger the change event to load coaches
        churchSelect.dispatchEvent(new Event('change'));
    }
});
<?php endif; ?>

<?php if (isset($userRole) && $userRole === 'coach'): ?>
// For coaches, the church and coach are already pre-selected and non-editable
document.addEventListener('DOMContentLoaded', function() {
    console.log('Coach editing mentor - church and coach are pre-selected');
});
<?php endif; ?>
</script> 