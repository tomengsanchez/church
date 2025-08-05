<?php $layout = 'layouts/authenticated'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fas fa-plus me-2"></i>Add New Church
                </h4>
            </div>
            <div class="card-body">
                <form method="POST" action="/church/create">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Church Name *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?= $data['name'] ?? '' ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?= $data['phone'] ?? '' ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?= $data['email'] ?? '' ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Address *</label>
                        <textarea class="form-control" id="address" name="address" rows="3" required><?= $data['address'] ?? '' ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="website" class="form-label">Website</label>
                            <input type="url" class="form-control" id="website" name="website" 
                                   value="<?= $data['website'] ?? '' ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="founded_date" class="form-label">Founded Date</label>
                            <input type="date" class="form-control" id="founded_date" name="founded_date" 
                                   value="<?= $data['founded_date'] ?? '' ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4"><?= $data['description'] ?? '' ?></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="/church" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Churches
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Create Church
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> 