<?php $layout = 'layouts/authenticated'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">
                <i class="fas fa-plus me-2"></i>Create Lifegroup
            </h1>
            <a href="/lifegroup" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Lifegroups
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form action="/lifegroup/create" method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Name *</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($data['name'] ?? '') ?>" required>
                            <div class="form-text">Enter a descriptive name for the lifegroup.</div>
                        </div>
                        
                        <?php if (isset($isSuperAdmin) && $isSuperAdmin): ?>
                        <div class="col-md-6 mb-3">
                            <label for="church_id" class="form-label">Church *</label>
                            <select class="form-select" id="church_id" name="church_id" required>
                                <option value="">Select a church</option>
                                <?php foreach ($churches as $church): ?>
                                <option value="<?= $church['id'] ?>" <?= ($data['church_id'] ?? '') == $church['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($church['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Select the church for this lifegroup.</div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="mentor_id" class="form-label">Mentor *</label>
                            <select class="form-select" id="mentor_id" name="mentor_id" required>
                                <option value="">Select a mentor</option>
                                <?php if (!isset($isSuperAdmin) || !$isSuperAdmin): ?>
                                <?php foreach ($mentors as $mentor): ?>
                                <option value="<?= $mentor['id'] ?>" <?= ($data['mentor_id'] ?? '') == $mentor['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($mentor['name']) ?>
                                </option>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <div class="form-text">Select the mentor who will lead this lifegroup.</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($data['description'] ?? '') ?></textarea>
                        <div class="form-text">Provide a brief description of the lifegroup's purpose and focus.</div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="meeting_day" class="form-label">Meeting Day</label>
                            <select class="form-select" id="meeting_day" name="meeting_day">
                                <option value="">Select day</option>
                                <option value="Monday" <?= ($data['meeting_day'] ?? '') === 'Monday' ? 'selected' : '' ?>>Monday</option>
                                <option value="Tuesday" <?= ($data['meeting_day'] ?? '') === 'Tuesday' ? 'selected' : '' ?>>Tuesday</option>
                                <option value="Wednesday" <?= ($data['meeting_day'] ?? '') === 'Wednesday' ? 'selected' : '' ?>>Wednesday</option>
                                <option value="Thursday" <?= ($data['meeting_day'] ?? '') === 'Thursday' ? 'selected' : '' ?>>Thursday</option>
                                <option value="Friday" <?= ($data['meeting_day'] ?? '') === 'Friday' ? 'selected' : '' ?>>Friday</option>
                                <option value="Saturday" <?= ($data['meeting_day'] ?? '') === 'Saturday' ? 'selected' : '' ?>>Saturday</option>
                                <option value="Sunday" <?= ($data['meeting_day'] ?? '') === 'Sunday' ? 'selected' : '' ?>>Sunday</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="meeting_time" class="form-label">Meeting Time</label>
                            <input type="time" class="form-control" id="meeting_time" name="meeting_time" value="<?= htmlspecialchars($data['meeting_time'] ?? '') ?>">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="max_members" class="form-label">Max Members</label>
                            <input type="number" class="form-control" id="max_members" name="max_members" value="<?= htmlspecialchars($data['max_members'] ?? '20') ?>" min="1" max="100">
                            <div class="form-text">Maximum number of members allowed in this lifegroup.</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="meeting_location" class="form-label">Meeting Location</label>
                        <input type="text" class="form-control" id="meeting_location" name="meeting_location" value="<?= htmlspecialchars($data['meeting_location'] ?? '') ?>">
                        <div class="form-text">Where the lifegroup will meet (e.g., Conference Room A, Fellowship Hall).</div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="/lifegroup" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Create Lifegroup
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if (isset($isSuperAdmin) && $isSuperAdmin): ?>
<script>
document.getElementById('church_id').addEventListener('change', function() {
    const churchId = this.value;
    const mentorSelect = document.getElementById('mentor_id');
    
    // Clear current options
    mentorSelect.innerHTML = '<option value="">Select a mentor</option>';
    
    if (churchId) {
        // Fetch mentors for the selected church
        fetch(`/lifegroup/mentors/${churchId}`)
            .then(response => response.json())
            .then(data => {
                data.forEach(mentor => {
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
<?php endif; ?> 