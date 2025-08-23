<?php $layout = 'layouts/authenticated'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h4 class="mb-0">
                    <i class="fas fa-user-plus me-2"></i>Add New Friend
                </h4>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Quick Add:</strong> Add a new friend with just their name. They will be marked as "Pending" and you can assign them to a church, coach, and mentor later.
                </div>
                <form method="POST" action="/friend">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?= htmlspecialchars($data['name'] ?? '') ?>" required>
                            <div class="form-text">Enter the friend's full name</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($data['email'] ?? '') ?>">
                            <div class="form-text">Optional: Email address for communication</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?= htmlspecialchars($data['phone'] ?? '') ?>">
                            <div class="form-text">Optional: Phone number for contact</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="church_id" class="form-label">Church</label>
                            <?php if (isset($userRole) && in_array($userRole, ['coach', 'mentor'])): ?>
                                <input type="hidden" name="church_id" value="<?= $churches[0]['id'] ?? '' ?>">
                                <input type="text" class="form-control" value="<?= htmlspecialchars($churches[0]['name'] ?? '') ?>" readonly>
                                <div class="form-text">Church is automatically set to your assigned church.</div>
                            <?php else: ?>
                                <select class="form-select" id="church_id" name="church_id">
                                    <option value="">Select Church (Optional)</option>
                                    <?php foreach ($churches as $church): ?>
                                    <option value="<?= $church['id'] ?>" <?= ($data['church_id'] ?? '') == $church['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($church['name'] ?? '') ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Optional: Assign to a specific church now, or leave empty to assign later</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Note:</strong> This friend will be created with "Pending" status. You can assign them to a coach, mentor, and lifegroup later by editing their profile.
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="/friend" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Friends
                        </a>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-user-plus me-2"></i>Add New Friend
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
