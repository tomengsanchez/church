<?php $layout = 'layouts/unauthenticated'; ?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white text-center">
                <h4 class="mb-0">
                    <i class="fas fa-user-plus me-2"></i>Register
                </h4>
            </div>
            <div class="card-body">
                <form method="POST" action="/auth/register">
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?= $data['name'] ?? '' ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= $data['email'] ?? '' ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="form-text">Password must be at least 6 characters long.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-user-tag"></i>
                            </span>
                            <select class="form-select" id="role" name="role" required>
                                <option value="member" <?= ($data['role'] ?? '') === 'member' ? 'selected' : '' ?>>Member</option>
                                <option value="mentor" <?= ($data['role'] ?? '') === 'mentor' ? 'selected' : '' ?>>Mentor</option>
                                <option value="coach" <?= ($data['role'] ?? '') === 'coach' ? 'selected' : '' ?>>Coach</option>
                                <option value="pastor" <?= ($data['role'] ?? '') === 'pastor' ? 'selected' : '' ?>>Pastor</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus me-2"></i>Register
                        </button>
                    </div>
                </form>
                
                <hr class="my-4">
                
                <div class="text-center">
                    <p class="mb-0">Already have an account? 
                        <a href="/auth/login" class="text-decoration-none">Login here</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div> 