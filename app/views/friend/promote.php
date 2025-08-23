<?php $layout = 'layouts/authenticated'; ?>

<div class="container-fluid form-container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fas fa-user-plus me-2"></i>
                        Promote Friend to Active Member
                    </h4>
                    <p class="card-subtitle text-muted">
                        Assign Church, Pastor, and Coach to promote "<?= htmlspecialchars($friend['name'] ?? '') ?>" to active member status
                    </p>
                </div>
                <div class="card-body">
                    <form method="POST" action="/friend/promote/<?= $friend['id'] ?>" class="promote-form">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Promotion Process:</strong> This will change the friend's status from "Pending" to "Active" and assign them to the selected Church, Pastor, and Coach. They will then appear in the regular Members list.
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="friend_name" class="form-label">Friend Name</label>
                                    <input type="text" class="form-control" id="friend_name" value="<?= htmlspecialchars($friend['name'] ?? '') ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="friend_email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="friend_email" value="<?= htmlspecialchars($friend['email'] ?? '') ?>" readonly>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <h5 class="mb-3">Assignment Details</h5>
                        
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="church_id" class="form-label">Church <span class="text-danger">*</span></label>
                                    <select class="form-select" id="church_id" name="church_id" required>
                                        <option value="">Select Church</option>
                                        <?php foreach ($churches as $church): ?>
                                            <option value="<?= $church['id'] ?>">
                                                <?= htmlspecialchars($church['name'] ?? '') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="pastor_id" class="form-label">Pastor <span class="text-danger">*</span></label>
                                    <select class="form-select" id="pastor_id" name="pastor_id" required>
                                        <option value="">Select Pastor</option>
                                        <?php foreach ($pastors as $pastor): ?>
                                            <option value="<?= $pastor['id'] ?>" data-church="<?= $pastor['church_id'] ?>">
                                                <?= htmlspecialchars($pastor['name'] ?? '') ?>
                                                <?php if (!empty($pastor['church_name'])): ?>
                                                    (<?= htmlspecialchars($pastor['church_name']) ?>)
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="coach_id" class="form-label">Coach <span class="text-danger">*</span></label>
                                    <select class="form-select" id="coach_id" name="coach_id" required>
                                        <option value="">Select Coach</option>
                                        <?php foreach ($coaches as $coach): ?>
                                            <option value="<?= $coach['id'] ?>" data-church="<?= $coach['church_id'] ?>">
                                                <?= htmlspecialchars($coach['name'] ?? '') ?>
                                                <?php if (!empty($coach['church_name'])): ?>
                                                    (<?= htmlspecialchars($coach['church_name']) ?>)
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="mentor_id" class="form-label">Mentor <small class="text-muted">(Optional)</small></label>
                                    <select class="form-select" id="mentor_id" name="mentor_id">
                                        <option value="">Select Mentor (Optional)</option>
                                        <?php foreach ($mentors as $mentor): ?>
                                            <option value="<?= $mentor['id'] ?>" data-church="<?= $mentor['church_id'] ?>" data-coach="<?= $mentor['coach_id'] ?? '' ?>">
                                                <?= htmlspecialchars($mentor['name'] ?? '') ?>
                                                <?php if (!empty($mentor['coach_name'])): ?>
                                                    (Coach: <?= htmlspecialchars($mentor['coach_name']) ?>)
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Important:</strong> Church, Pastor, and Coach are required for promotion. Mentor is optional. The Pastor, Coach, and Mentor (if selected) must belong to the selected Church. If a Mentor is selected, they must be assigned to the selected Coach.
                        </div>
                        
                        <div class="d-flex justify-content-between mb-4">
                            <a href="/friend" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-user-check me-2"></i>Promote to Active Member
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const churchSelect = document.getElementById('church_id');
    const pastorSelect = document.getElementById('pastor_id');
    const coachSelect = document.getElementById('coach_id');
    const mentorSelect = document.getElementById('mentor_id');
    
    // Filter pastors, coaches, and mentors based on selected church
    function filterByChurch() {
        const selectedChurchId = churchSelect.value;
        
        // Filter pastors
        Array.from(pastorSelect.options).forEach(option => {
            if (option.value === '') return; // Skip the placeholder option
            const churchId = option.getAttribute('data-church');
            option.style.display = !selectedChurchId || churchId === selectedChurchId ? '' : 'none';
        });
        
        // Filter coaches
        Array.from(coachSelect.options).forEach(option => {
            if (option.value === '') return; // Skip the placeholder option
            const churchId = option.getAttribute('data-church');
            option.style.display = !selectedChurchId || churchId === selectedChurchId ? '' : 'none';
        });
        
        // Filter mentors
        Array.from(mentorSelect.options).forEach(option => {
            if (option.value === '') return; // Skip the placeholder option
            const churchId = option.getAttribute('data-church');
            option.style.display = !selectedChurchId || churchId === selectedChurchId ? '' : 'none';
        });
        
        // Reset selections if they're no longer valid
        if (pastorSelect.value && pastorSelect.selectedOptions[0].style.display === 'none') {
            pastorSelect.value = '';
        }
        if (coachSelect.value && coachSelect.selectedOptions[0].style.display === 'none') {
            coachSelect.value = '';
        }
        if (mentorSelect.value && mentorSelect.selectedOptions[0].style.display === 'none') {
            mentorSelect.value = '';
        }
        
        // When church changes, also filter mentors by coach
        filterMentorsByCoach();
    }
    
    // Filter mentors based on selected coach
    function filterMentorsByCoach() {
        const selectedCoachId = coachSelect.value;
        
        Array.from(mentorSelect.options).forEach(option => {
            if (option.value === '') return; // Skip the placeholder option
            const coachId = option.getAttribute('data-coach');
            // If no coach selected, show all mentors from the selected church
            // If coach is selected, only show mentors assigned to that coach
            option.style.display = !selectedCoachId || coachId === selectedCoachId ? '' : 'none';
        });
        
        // Reset mentor selection if it's no longer valid
        if (mentorSelect.value && mentorSelect.selectedOptions[0].style.display === 'none') {
            mentorSelect.value = '';
        }
    }
    
    churchSelect.addEventListener('change', filterByChurch);
    coachSelect.addEventListener('change', filterMentorsByCoach);
    
    // Initial filter
    filterByChurch();
});
</script>
