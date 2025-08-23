<?php $layout = 'layouts/authenticated'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">

        
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fas fa-edit me-2"></i>Edit Member
                </h4>
            </div>
            <div class="card-body">
                <?php if ($member['status'] === 'pending'): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Pending Friend:</strong> This member is currently pending. Please assign them to a church, coach, and mentor, then change their status to "Active" to complete their profile.
                </div>
                <?php endif; ?>
                
                <form method="POST" action="/member/edit/<?= $member['id'] ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?= htmlspecialchars($member['name'] ?? '') ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php 
                                   $email = $member['email'] ?? '';
                                   if (strpos($email, 'no-email-') === 0 && strpos($email, '@placeholder.local') !== false) {
                                       echo '';
                                   } else {
                                       echo htmlspecialchars($email);
                                   }
                                   ?>">
                            <div class="form-text">Optional: Email address for member communication.</div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?= htmlspecialchars($member['phone'] ?? '') ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="church_id" class="form-label">Church *</label>
                            <?php if (isset($userRole) && in_array($userRole, ['coach', 'mentor'])): ?>
                                <!-- For coaches and mentors, church is pre-selected and non-editable -->
                                <input type="hidden" name="church_id" value="<?= $churches[0]['id'] ?? '' ?>">
                                <input type="text" class="form-control" value="<?= htmlspecialchars($churches[0]['name'] ?? '') ?>" readonly>
                                <div class="form-text">Church is automatically set to your assigned church.</div>
                            <?php else: ?>
                                <select class="form-select" id="church_id" name="church_id" required>
                                    <option value="">Select Church</option>
                                    <?php foreach ($churches as $church): ?>
                                    <option value="<?= $church['id'] ?>" <?= $member['church_id'] == $church['id'] ? 'selected' : '' ?>>
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
                            <?php if (isset($userRole) && in_array($userRole, ['coach', 'mentor'])): ?>
                                <!-- For coaches and mentors, coach is pre-selected and non-editable -->
                                <input type="hidden" name="coach_id" value="<?= $coaches[0]['id'] ?? '' ?>">
                                <input type="text" class="form-control" value="<?= htmlspecialchars($coaches[0]['name'] ?? '') ?>" readonly>
                                <div class="form-text">Coach is automatically set to your assigned coach.</div>
                            <?php else: ?>
                                <select class="form-select" id="coach_id" name="coach_id">
                                    <option value="">Select Coach</option>
                                    <?php if (isset($coaches) && !empty($coaches)): ?>
                                        <?php foreach ($coaches as $coach): ?>
                                        <option value="<?= $coach['id'] ?>" <?= ($currentCoach && $currentCoach['id'] == $member['coach_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($coach['name'] ?? '') ?>
                                        </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <div class="form-text">Optional: Assign a coach to this member. Select a church first.</div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="mentor_id" class="form-label">Mentor</label>
                            <?php if (isset($userRole) && $userRole === 'mentor'): ?>
                                <!-- For mentors, show dropdown with themselves and other mentors under their coach -->
                                <select class="form-select" id="mentor_id" name="mentor_id">
                                    <option value="">Select Mentor</option>
                                    <?php if (!empty($mentors)): ?>
                                        <?php foreach ($mentors as $mentor): ?>
                                            <option value="<?= $mentor['id'] ?>" <?= ($member['mentor_id'] ?? $_SESSION['user_id']) == $mentor['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($mentor['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <div class="form-text">Select a mentor for this member. You can assign to yourself or other mentors under your coach.</div>
                            <?php else: ?>
                                <select class="form-select" id="mentor_id" name="mentor_id">
                                    <option value="">Select Mentor</option>
                                </select>
                                <div class="form-text">Optional: Assign a mentor to this member. Select a coach first.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="lifegroup_id" class="form-label">Lifegroup</label>
                            <?php if (isset($userRole) && $userRole === 'mentor'): ?>
                                <!-- For mentors, show dropdown with only their assigned lifegroups -->
                                <?php if (!empty($lifegroups)): ?>
                                    <select class="form-select" id="lifegroup_id" name="lifegroup_id">
                                        <option value="">Select Lifegroup</option>
                                        <?php foreach ($lifegroups as $lifegroup): ?>
                                            <option value="<?= $lifegroup['id'] ?>" <?= ($member['lifegroup_id'] ?? '') == $lifegroup['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($lifegroup['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">Select from your assigned lifegroups.</div>
                                <?php else: ?>
                                    <input type="text" class="form-control" value="No lifegroup assigned" readonly>
                                    <div class="form-text">You don't have any assigned lifegroups.</div>
                                <?php endif; ?>
                            <?php else: ?>
                                <select class="form-select" id="lifegroup_id" name="lifegroup_id">
                                    <option value="">Select Lifegroup</option>
                                </select>
                                <div class="form-text">Optional: Assign this member to a lifegroup. Select a mentor first.</div>
                            <?php endif; ?>
                            

                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status *</label>
                            <select class="form-select" id="status" name="status" required>
                                <?php if (!empty($memberStatuses ?? [])): ?>
                                    <?php foreach ($memberStatuses as $status): ?>
                                        <?php if ((int)$status['is_active'] === 1): ?>
                                            <option value="<?= htmlspecialchars($status['slug']) ?>" <?= ($member['status'] === $status['slug']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($status['name']) ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="active" <?= $member['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="inactive" <?= $member['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                    <option value="pending" <?= $member['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="suspended" <?= $member['status'] === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?= htmlspecialchars($member['address'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="password" name="password">
                            <div class="form-text">Leave blank to keep current password.</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="/member" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Members
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Member
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

// Get previous form values (for validation errors) and current assignments
const previousValues = {
    church_id: '<?= $_POST['church_id'] ?? $member['church_id'] ?? '' ?>',
    coach_id: '<?= $_POST['coach_id'] ?? ($currentCoach ? $currentCoach['id'] : '') ?? '' ?>',
    mentor_id: '<?= $_POST['mentor_id'] ?? ($currentMentor ? $currentMentor['id'] : '') ?? '' ?>',
    lifegroup_id: '<?= $_POST['lifegroup_id'] ?? ($currentLifegroup ? $currentLifegroup['lifegroup_id'] : '') ?? '' ?>'
};

// Filter coaches based on selected church
const churchSelect = document.getElementById('church_id');
if (churchSelect) {
    churchSelect.addEventListener('change', function() {
        const churchId = this.value;
        const coachSelect = document.getElementById('coach_id');
        const mentorSelect = document.getElementById('mentor_id');
        const lifegroupSelect = document.getElementById('lifegroup_id');
        
        // Skip if user is a coach (fields are non-editable)
        if (document.querySelector('input[name="coach_id"]')) {
            return;
        }
        
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
}

// Filter mentors based on selected coach
const coachSelect = document.getElementById('coach_id');
if (coachSelect) {
    coachSelect.addEventListener('change', function() {
        const coachId = this.value;
        loadMentorsForCoach(coachId);
    });
}

// Filter lifegroups based on selected mentor
const mentorSelect = document.getElementById('mentor_id');
if (mentorSelect) {
    mentorSelect.addEventListener('change', function() {
        const mentorId = this.value;
        loadLifegroupsForMentor(mentorId);
    });
}

// Helper function to load mentors for a coach
function loadMentorsForCoach(coachId) {

    const mentorSelect = document.getElementById('mentor_id');
    const lifegroupSelect = document.getElementById('lifegroup_id');
    
    // Only proceed if mentor select element exists
    if (!mentorSelect) {
        return;
    }
    
    // Reset lifegroup selection
    if (lifegroupSelect) {
        lifegroupSelect.innerHTML = '<option value="">Select Lifegroup</option>';
    }
    
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
                    // Select if this was the previously selected mentor
                    if (mentor.id == previousValues.mentor_id) {
                        option.selected = true;
                    }
                    mentorSelect.appendChild(option);
                });
                
                // If we had a previous mentor selection, load lifegroups for that mentor
                if (previousValues.mentor_id) {
                    loadLifegroupsForMentor(previousValues.mentor_id);
                } else if (mentors.length > 0) {
                    // If no mentor was pre-selected but we have mentors, load lifegroups for the first one
                    // This helps coaches see available lifegroups even for new members
                    loadLifegroupsForMentor(mentors[0].id);
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
    
    // Only proceed if lifegroup select element exists
    if (!lifegroupSelect) {
        return;
    }
    
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
    
    // Check if user is a coach (fields are non-editable)
    const isCoach = document.querySelector('input[name="coach_id"]');
    
    if (isCoach) {
        // For coaches, load mentors and lifegroups based on their pre-selected coach ID
        const coachId = isCoach.value;
        if (coachId) {
            loadMentorsForCoach(coachId);
        }
    } else {
        // For non-coaches, trigger the normal dropdown loading flow
        if (previousValues.church_id && churchSelect.value === previousValues.church_id) {
            churchSelect.dispatchEvent(new Event('change'));
        }
    }
});
</script> 