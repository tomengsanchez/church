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
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= $data['email'] ?? '' ?>">
                            <div class="form-text">Optional: Email address for member communication.</div>
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
                            <label for="lifegroup_id" class="form-label">Lifegroup</label>
                            <select class="form-select" id="lifegroup_id" name="lifegroup_id">
                                <option value="">Select Lifegroup</option>
                            </select>
                            <div class="form-text">Optional: Assign this member to a lifegroup. Select a mentor first.</div>
                        </div>
                        
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
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password">
                            <div class="form-text">Optional: Password for member login. Must be at least 6 characters if provided.</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
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

// Get previous form values (for validation errors)
const previousValues = {
    church_id: '<?= $data['church_id'] ?? '' ?>',
    coach_id: '<?= $data['coach_id'] ?? '' ?>',
    mentor_id: '<?= $data['mentor_id'] ?? '' ?>',
    lifegroup_id: '<?= $data['lifegroup_id'] ?? '' ?>'
};

// Filter coaches based on selected church
document.getElementById('church_id').addEventListener('change', function() {
    const churchId = this.value;
    const coachSelect = document.getElementById('coach_id');
    const mentorSelect = document.getElementById('mentor_id');
    const lifegroupSelect = document.getElementById('lifegroup_id');
    
    // Reset selections
    coachSelect.innerHTML = '<option value="">Select Coach</option>';
    mentorSelect.innerHTML = '<option value="">Select Mentor</option>';
    lifegroupSelect.innerHTML = '<option value="">Select Lifegroup</option>';
    
    if (churchId) {
        // Fetch coaches for the selected church
        fetch(`/member/coaches/${churchId}`)
            .then(response => response.json())
            .then(coaches => {
                coaches.forEach(coach => {
                    const option = document.createElement('option');
                    option.value = coach.id;
                    option.textContent = coach.name;
                    // Select if this was the previously selected coach
                    if (coach.id == previousValues.coach_id) {
                        option.selected = true;
                    }
                    coachSelect.appendChild(option);
                });
                
                // If we had a previous coach selection, load mentors for that coach
                if (previousValues.coach_id) {
                    loadMentorsForCoach(previousValues.coach_id);
                }
            })
            .catch(error => {
                console.error('Error fetching coaches:', error);
            });
    }
});

// Filter mentors based on selected coach
document.getElementById('coach_id').addEventListener('change', function() {
    const coachId = this.value;
    loadMentorsForCoach(coachId);
});

// Filter lifegroups based on selected mentor
document.getElementById('mentor_id').addEventListener('change', function() {
    const mentorId = this.value;
    loadLifegroupsForMentor(mentorId);
});

// Helper function to load mentors for a coach
function loadMentorsForCoach(coachId) {
    const mentorSelect = document.getElementById('mentor_id');
    const lifegroupSelect = document.getElementById('lifegroup_id');
    
    // Reset mentor and lifegroup selections
    mentorSelect.innerHTML = '<option value="">Select Mentor</option>';
    lifegroupSelect.innerHTML = '<option value="">Select Lifegroup</option>';
    
    if (coachId) {
        // Fetch mentors for the selected coach
        fetch(`/member/mentors-by-coach/${coachId}`)
            .then(response => response.json())
            .then(mentors => {
                mentors.forEach(mentor => {
                    const option = document.createElement('option');
                    option.value = mentor.id;
                    option.textContent = mentor.name;
                    // Select if this was the previously selected mentor
                    if (mentor.id == previousValues.mentor_id) {
                        option.selected = true;
                    }
                    mentorSelect.appendChild(option);
                });
                
                // If we had a previous mentor selection, load lifegroups for that mentor
                if (previousValues.mentor_id) {
                    loadLifegroupsForMentor(previousValues.mentor_id);
                }
            })
            .catch(error => {
                console.error('Error fetching mentors:', error);
            });
    }
}

// Helper function to load lifegroups for a mentor
function loadLifegroupsForMentor(mentorId) {
    const lifegroupSelect = document.getElementById('lifegroup_id');
    
    // Reset lifegroup selection
    lifegroupSelect.innerHTML = '<option value="">Select Lifegroup</option>';
    
    if (mentorId) {
        // Fetch lifegroups for the selected mentor
        fetch(`/member/lifegroups-by-mentor/${mentorId}`)
            .then(response => response.json())
            .then(lifegroups => {
                lifegroups.forEach(lifegroup => {
                    const option = document.createElement('option');
                    option.value = lifegroup.id;
                    option.textContent = lifegroup.name;
                    // Select if this was the previously selected lifegroup
                    if (lifegroup.id == previousValues.lifegroup_id) {
                        option.selected = true;
                    }
                    lifegroupSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error fetching lifegroups:', error);
            });
    }
}

// Load dropdowns on page load if we have previous values
document.addEventListener('DOMContentLoaded', function() {
    const churchSelect = document.getElementById('church_id');
    
    // If we have a previous church selection, trigger the change event
    if (previousValues.church_id && churchSelect.value === previousValues.church_id) {
        churchSelect.dispatchEvent(new Event('change'));
    }
});
</script> 