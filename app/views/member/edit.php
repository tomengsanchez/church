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
                <form method="POST" action="/member/edit/<?= $member['id'] ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?= htmlspecialchars($member['name']) ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($member['email']) ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?= htmlspecialchars($member['phone']) ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="church_id" class="form-label">Church *</label>
                            <select class="form-select" id="church_id" name="church_id" required>
                                <option value="">Select Church</option>
                                <?php foreach ($churches as $church): ?>
                                <option value="<?= $church['id'] ?>" <?= $member['church_id'] == $church['id'] ? 'selected' : '' ?>>
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
                                <option value="<?= $pastor['id'] ?>" <?= $member['pastor_id'] == $pastor['id'] ? 'selected' : '' ?>>
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
                                <option value="<?= $coach['id'] ?>" <?= $member['coach_id'] == $coach['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($coach['name']) ?> (<?= htmlspecialchars($coach['church_name']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="mentor_id" class="form-label">Mentor *</label>
                            <select class="form-select" id="mentor_id" name="mentor_id" required>
                                <option value="">Select Mentor</option>
                                <?php foreach ($mentors as $mentor): ?>
                                <option value="<?= $mentor['id'] ?>" <?= $member['mentor_id'] == $mentor['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($mentor['name']) ?> (<?= htmlspecialchars($mentor['church_name']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status *</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active" <?= $member['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $member['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                <option value="pending" <?= $member['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="suspended" <?= $member['status'] === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                            </select>
                        </div>
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
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" 
                                   value="<?= htmlspecialchars($member['date_of_birth']) ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="gender" class="form-label">Gender</label>
                            <select class="form-select" id="gender" name="gender">
                                <option value="">Select Gender</option>
                                <option value="male" <?= $member['gender'] === 'male' ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= $member['gender'] === 'female' ? 'selected' : '' ?>>Female</option>
                                <option value="other" <?= $member['gender'] === 'other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?= htmlspecialchars($member['address']) ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="emergency_contact" class="form-label">Emergency Contact</label>
                            <input type="text" class="form-control" id="emergency_contact" name="emergency_contact" 
                                   value="<?= htmlspecialchars($member['emergency_contact']) ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="emergency_phone" class="form-label">Emergency Phone</label>
                            <input type="tel" class="form-control" id="emergency_phone" name="emergency_phone" 
                                   value="<?= htmlspecialchars($member['emergency_phone']) ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="occupation" class="form-label">Occupation</label>
                            <input type="text" class="form-control" id="occupation" name="occupation" 
                                   value="<?= htmlspecialchars($member['occupation']) ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="marital_status" class="form-label">Marital Status</label>
                            <select class="form-select" id="marital_status" name="marital_status">
                                <option value="">Select Status</option>
                                <option value="single" <?= $member['marital_status'] === 'single' ? 'selected' : '' ?>>Single</option>
                                <option value="married" <?= $member['marital_status'] === 'married' ? 'selected' : '' ?>>Married</option>
                                <option value="divorced" <?= $member['marital_status'] === 'divorced' ? 'selected' : '' ?>>Divorced</option>
                                <option value="widowed" <?= $member['marital_status'] === 'widowed' ? 'selected' : '' ?>>Widowed</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"><?= htmlspecialchars($member['notes']) ?></textarea>
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
</script> 