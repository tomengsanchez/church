<?php $layout = 'layouts/authenticated'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fas fa-plus me-2"></i>Add New Coach
                </h4>
            </div>
            <div class="card-body">
                <form method="POST" action="/coach/create">
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
                            <label for="satelife_name" class="form-label">Satelife Name</label>
                            <input type="text" class="form-control" id="satelife_name" name="satelife_name" 
                                   value="<?= $data['satelife_name'] ?? '' ?>" placeholder="Optional ministry name">
                            <div class="form-text">Optional name for your Satelife ministry (e.g., "Youth Ministry", "Children's Ministry")</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?= $data['phone'] ?? '' ?>">
                        </div>
                    </div>
                    
                    <div class="row">
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
                        
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password *</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="form-text">Password must be at least 6 characters long.</div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password *</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <!-- Empty column for layout balance -->
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?= $data['address'] ?? '' ?></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="/coach" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Coaches
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Create Coach
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

// Filter pastors based on selected church
document.getElementById('church_id').addEventListener('change', function() {
    const churchId = this.value;
    const pastorSelect = document.getElementById('pastor_id');
    
    // Reset pastor selection
    pastorSelect.innerHTML = '<option value="">Select Pastor</option>';
    
    if (churchId) {
        // Filter pastors by church (this would need AJAX in a real implementation)
        // For now, we'll just show all pastors
    }
});
</script> 