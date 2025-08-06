<?php $layout = 'layouts/authenticated'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fas fa-plus me-2"></i>Add New Member
                </h4>
            </div>
            <div class="card-body">
                <form method="POST" action="/member/create">
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
                                    <?= htmlspecialchars($church['name'] ?? '') ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="coach_id" class="form-label">Coach</label>
                            <select class="form-select" id="coach_id" name="coach_id">
                                <option value="">Select Coach</option>
                            </select>
                            <div class="form-text">Optional: Assign a coach to this member. Select a church first.</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="mentor_id" class="form-label">Mentor</label>
                            <select class="form-select" id="mentor_id" name="mentor_id">
                                <option value="">Select Mentor</option>
                            </select>
                            <div class="form-text">Optional: Assign a mentor to this member. Select a coach first.</div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status *</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active" <?= ($data['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= ($data['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                <option value="pending" <?= ($data['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="suspended" <?= ($data['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?= $data['address'] ?? '' ?></textarea>
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
                    
                    <div class="d-flex justify-content-between">
                        <a href="/member" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Members
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Create Member
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

// Filter coaches based on selected church
document.getElementById('church_id').addEventListener('change', function() {
    const churchId = this.value;
    const coachSelect = document.getElementById('coach_id');
    const mentorSelect = document.getElementById('mentor_id');
    
    // Reset selections
    coachSelect.innerHTML = '<option value="">Select Coach</option>';
    mentorSelect.innerHTML = '<option value="">Select Mentor</option>';
    
    if (churchId) {
        // Fetch coaches for the selected church
        fetch(`/member/coaches/${churchId}`)
            .then(response => response.json())
            .then(coaches => {
                coaches.forEach(coach => {
                    const option = document.createElement('option');
                    option.value = coach.id;
                    option.textContent = coach.name;
                    coachSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error fetching coaches:', error);
            });
    }
});

// Filter mentors based on selected coach
document.getElementById('coach_id').addEventListener('change', function() {
    const coachId = this.value;
    const mentorSelect = document.getElementById('mentor_id');
    
    // Reset mentor selection
    mentorSelect.innerHTML = '<option value="">Select Mentor</option>';
    
    if (coachId) {
        // Fetch mentors for the selected coach
        fetch(`/member/mentors-by-coach/${coachId}`)
            .then(response => response.json())
            .then(mentors => {
                mentors.forEach(mentor => {
                    const option = document.createElement('option');
                    option.value = mentor.id;
                    option.textContent = mentor.name;
                    mentorSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error fetching mentors:', error);
            });
    }
});
</script> 