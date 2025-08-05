<?php $layout = 'layouts/authenticated'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fas fa-plus me-2"></i>Add New Mentor
                </h4>
            </div>
            <div class="card-body">
                <form method="POST" action="/mentor/create">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?= $data['name'] ?? '' ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= $data['email'] ?? '' ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?= $data['phone'] ?? '' ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="church_id" class="form-label">Church *</label>
                            <select class="form-select" id="church_id" name="church_id" required>
                                <option value="">Select Church</option>
                                <?php foreach ($churches as $church): ?>
                                <option value="<?= $church['id'] ?>" <?= ($data['church_id'] ?? '') == $church['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($church['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="pastor_id" class="form-label">Pastor *</label>
                            <select class="form-select" id="pastor_id" name="pastor_id" required>
                                <option value="">Select Pastor</option>
                                <?php foreach ($pastors as $pastor): ?>
                                <option value="<?= $pastor['id'] ?>" <?= ($data['pastor_id'] ?? '') == $pastor['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($pastor['name']) ?> (<?= htmlspecialchars($pastor['church_name']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="coach_id" class="form-label">Coach *</label>
                            <select class="form-select" id="coach_id" name="coach_id" required>
                                <option value="">Select Coach</option>
                                <?php foreach ($coaches as $coach): ?>
                                <option value="<?= $coach['id'] ?>" <?= ($data['coach_id'] ?? '') == $coach['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($coach['name']) ?> (<?= htmlspecialchars($coach['church_name']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password *</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="form-text">Password must be at least 6 characters long.</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password *</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="specialization" class="form-label">Specialization</label>
                            <input type="text" class="form-control" id="specialization" name="specialization" 
                                   value="<?= $data['specialization'] ?? '' ?>" placeholder="e.g., Youth Ministry, Counseling">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="experience_years" class="form-label">Years of Experience</label>
                            <input type="number" class="form-control" id="experience_years" name="experience_years" 
                                   value="<?= $data['experience_years'] ?? '' ?>" min="0" max="50">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?= $data['address'] ?? '' ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" 
                                   value="<?= $data['date_of_birth'] ?? '' ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="joining_date" class="form-label">Joining Date</label>
                            <input type="date" class="form-control" id="joining_date" name="joining_date" 
                                   value="<?= $data['joining_date'] ?? '' ?>">
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="/mentor" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Mentors
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Create Mentor
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
    
    if (password !== confirmPassword) {
        this.setCustomValidity('Passwords do not match');
    } else {
        this.setCustomValidity('');
    }
});

// Filter coaches based on selected pastor
document.getElementById('pastor_id').addEventListener('change', function() {
    const pastorId = this.value;
    const coachSelect = document.getElementById('coach_id');
    
    // Reset coach selection
    coachSelect.innerHTML = '<option value="">Select Coach</option>';
    
    if (pastorId) {
        // Filter coaches by pastor (this would need AJAX in a real implementation)
        // For now, we'll just show all coaches
    }
});
</script> 