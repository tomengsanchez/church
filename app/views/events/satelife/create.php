<?php $layout = 'layouts/authenticated'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">
                <i class="fas fa-plus me-2"></i>Add Satelife Event
            </h1>
            <a href="/events/satelife" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Events
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-plus me-2"></i>Event Details
                </h5>
            </div>
            <div class="card-body">
                <form action="/events/satelife/create" method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="title" class="form-label">Event Title *</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="church_id" class="form-label">Church *</label>
                                <select class="form-select" id="church_id" name="church_id" required onchange="loadCoaches()">
                                    <option value="">Select Church</option>
                                    <?php foreach ($churches as $church): ?>
                                        <option value="<?= $church['id'] ?>" <?= ($church['id'] == $defaultChurchId) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($church['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="coach_id" class="form-label">Coach/Satelife *</label>
                                <select class="form-select" id="coach_id" name="coach_id" required>
                                    <option value="">Select Coach</option>
                                    <?php if (!empty($coaches)): ?>
                                        <?php foreach ($coaches as $coach): ?>
                                            <option value="<?= $coach['id'] ?>" <?= ($coach['id'] == $defaultCoachId) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($coach['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="event_date" class="form-label">Event Date *</label>
                                <input type="date" class="form-control" id="event_date" name="event_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="event_time" class="form-label">Event Time</label>
                                <select class="form-select" id="event_time" name="event_time">
                                    <option value="">Select Time</option>
                                    <option value="01:00">1:00 AM</option>
                                    <option value="02:00">2:00 AM</option>
                                    <option value="03:00">3:00 AM</option>
                                    <option value="04:00">4:00 AM</option>
                                    <option value="05:00">5:00 AM</option>
                                    <option value="06:00">6:00 AM</option>
                                    <option value="07:00">7:00 AM</option>
                                    <option value="08:00">8:00 AM</option>
                                    <option value="09:00">9:00 AM</option>
                                    <option value="10:00">10:00 AM</option>
                                    <option value="11:00">11:00 AM</option>
                                    <option value="12:00">12:00 PM</option>
                                    <option value="13:00">1:00 PM</option>
                                    <option value="14:00">2:00 PM</option>
                                    <option value="15:00">3:00 PM</option>
                                    <option value="16:00">4:00 PM</option>
                                    <option value="17:00">5:00 PM</option>
                                    <option value="18:00">6:00 PM</option>
                                    <option value="19:00">7:00 PM</option>
                                    <option value="20:00">8:00 PM</option>
                                    <option value="21:00">9:00 PM</option>
                                    <option value="22:00">10:00 PM</option>
                                    <option value="23:00">11:00 PM</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="location" name="location" placeholder="e.g., Main Hall, Conference Room">
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Event description..."></textarea>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <h6 class="mb-3">
                            <i class="fas fa-users me-2"></i>Member Attendance
                        </h6>
                        <p class="text-muted">Search and add members who attended this satelife event:</p>
                        
                        <!-- Search Section -->
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="memberSearch" placeholder="Search for members by name or email...">
                                            <button class="btn btn-outline-secondary" type="button" onclick="searchMembers()">
                                                <i class="fas fa-search"></i> Search
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="button" class="btn btn-outline-primary" onclick="showAddMemberModal()">
                                            <i class="fas fa-plus"></i> Add New Member
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Search Results -->
                        <div id="searchResults" class="mb-3" style="display: none;">
                            <h6>Search Results</h6>
                            <div id="searchResultsList" class="list-group"></div>
                        </div>

                        <!-- Selected Members -->
                        <div id="selectedMembers" class="mb-3">
                            <h6>Selected Members for Attendance</h6>
                            <div id="selectedMembersList" class="list-group">
                                <div class="text-center text-muted">
                                    <i class="fas fa-users-slash fa-2x mb-2"></i>
                                    <p>No members selected yet. Use the search above to add members.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                                         <div class="d-flex justify-content-end">
                         <a href="/events/satelife" class="btn btn-secondary me-2">Cancel</a>
                                                   <button type="submit" class="btn btn-primary">
                              <i class="fas fa-save me-2"></i>Create Event
                          </button>
                     </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Member Modal -->
<div class="modal fade" id="addMemberModal" tabindex="-1" aria-labelledby="addMemberModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addMemberModalLabel">Add New Member to Satelife</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addMemberForm">
                    <div class="mb-3">
                        <label for="newMemberName" class="form-label">Name *</label>
                        <input type="text" class="form-control" id="newMemberName" required>
                    </div>
                                         <div class="mb-3">
                         <label for="newMemberEmail" class="form-label">Email</label>
                         <input type="email" class="form-control" id="newMemberEmail">
                     </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        This member will be added with "Pending" status and assigned to the selected Coach/Satelife.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addNewMember()">Add Member</button>
            </div>
        </div>
    </div>
</div>

<script>
let selectedMembers = new Set();

// Set default date to today
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('event_date').value = today;
    
    // Initialize selected members from existing checkboxes
    document.querySelectorAll('input[name="attendees[]"]').forEach(checkbox => {
        if (checkbox.checked) {
            selectedMembers.add(checkbox.value);
        }
    });
});

function loadCoaches() {
    const churchId = document.getElementById('church_id').value;
    const coachSelect = document.getElementById('coach_id');
    
    if (!churchId) {
        coachSelect.innerHTML = '<option value="">Select Coach</option>';
        return;
    }
    
    // Show loading state
    coachSelect.innerHTML = '<option value="">Loading coaches...</option>';
    
    fetch('/events/satelife/getCoachesByChurch', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `church_id=${churchId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            console.error('Error:', data.error);
            coachSelect.innerHTML = '<option value="">Error loading coaches</option>';
            return;
        }
        
        // Clear and populate coach dropdown
        coachSelect.innerHTML = '<option value="">Select Coach</option>';
        
        if (data && data.length > 0) {
            data.forEach(coach => {
                const option = document.createElement('option');
                option.value = coach.id;
                option.textContent = coach.name;
                coachSelect.appendChild(option);
            });
        } else {
            coachSelect.innerHTML = '<option value="">No coaches found</option>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        coachSelect.innerHTML = '<option value="">Error loading coaches</option>';
    });
}

function loadMembers() {
    // This function is intentionally empty - we don't want to auto-load members
    // Users should use the search functionality to add members manually
}

function searchMembers() {
    console.log('searchMembers called');
    const searchTerm = document.getElementById('memberSearch').value.trim();
    const churchId = document.getElementById('church_id').value;
    const coachId = document.getElementById('coach_id').value;
    
    console.log('Search params:', {searchTerm, churchId, coachId});
    
    if (searchTerm.length < 2) {
        alert('Please enter at least 2 characters to search');
        return;
    }
    
    fetch('/events/satelife/searchMembers', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `search=${encodeURIComponent(searchTerm)}&church_id=${churchId}&coach_id=${coachId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert('Error: ' + data.error);
            return;
        }
        
        displaySearchResults(data);
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error searching for members');
    });
}

function displaySearchResults(members) {
    console.log('displaySearchResults called with:', members.length, 'members');
    const searchResults = document.getElementById('searchResults');
    const searchResultsList = document.getElementById('searchResultsList');
    
    if (!members || members.length === 0) {
        console.log('No members found in search results');
        searchResultsList.innerHTML = '<div class="text-center text-muted">No members found</div>';
        searchResults.style.display = 'block';
        return;
    }
    
    let html = '';
    members.forEach(member => {
        const isSelected = selectedMembers.has(member.id.toString());
        const buttonText = isSelected ? 'Remove from Attendance' : 'Add to Attendance';
        const buttonClass = isSelected ? 'btn-danger' : 'btn-success';
        
        html += `
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <strong>${member.name}</strong>
                    <br>
                    <small class="text-muted">
                        ${member.email}
                        ${member.mentor_name ? '<br>Mentor: ' + member.mentor_name : ''}
                        ${member.coach_name ? '<br>Coach: ' + member.coach_name : ''}
                    </small>
                </div>
                <button type="button" class="btn btn-sm ${buttonClass}" onclick="toggleMemberAttendance(${member.id}, '${member.name}', '${member.email}')">
                    ${buttonText}
                </button>
            </div>
        `;
    });
    
    searchResultsList.innerHTML = html;
    searchResults.style.display = 'block';
}

function toggleMemberAttendance(memberId, memberName, memberEmail) {
    console.log('toggleMemberAttendance called for:', memberName);
    const memberIdStr = memberId.toString();
    
    if (selectedMembers.has(memberIdStr)) {
        // Remove from attendance
        console.log('Removing member from attendance:', memberName);
        selectedMembers.delete(memberIdStr);
        removeMemberFromList(memberId);
    } else {
        // Add to attendance
        console.log('Adding member to attendance:', memberName);
        selectedMembers.add(memberIdStr);
        addMemberToList({id: memberId, name: memberName, email: memberEmail}, false);
    }
    
    // Refresh search results
    console.log('Refreshing search results...');
    searchMembers();
}

function addMemberToList(member, isExisting = false) {
    console.log('addMemberToList called:', member.name, 'isExisting:', isExisting);
    const selectedMembersList = document.getElementById('selectedMembersList');
    const memberIdStr = member.id.toString();
    
    // Check if member is already in the list
    if (document.querySelector(`[data-member-id="${member.id}"]`)) {
        console.log('Member already in list:', member.name);
        return;
    }
    
    const memberHtml = `
        <div class="list-group-item d-flex justify-content-between align-items-center" data-member-id="${member.id}">
            <div>
                <strong>${member.name}</strong>
                <br>
                <small class="text-muted">
                    ${member.email}
                    ${member.mentor_name ? '<br>Mentor: ' + member.mentor_name : ''}
                </small>
            </div>
            <div>
                <input type="checkbox" name="attendees[]" value="${member.id}" checked>
                <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="removeMember(${member.id})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    
    // Remove the "no members" message if it exists
    const noMembersMessage = selectedMembersList.querySelector('.text-center.text-muted');
    if (noMembersMessage) {
        noMembersMessage.remove();
    }
    
    selectedMembersList.insertAdjacentHTML('beforeend', memberHtml);
}

function removeMember(memberId) {
    const memberIdStr = memberId.toString();
    selectedMembers.delete(memberIdStr);
    
    const memberElement = document.querySelector(`[data-member-id="${memberId}"]`);
    if (memberElement) {
        memberElement.remove();
    }
    
    // Check if no members left
    const selectedMembersList = document.getElementById('selectedMembersList');
    if (selectedMembersList.children.length === 0) {
        selectedMembersList.innerHTML = `
            <div class="text-center text-muted">
                <i class="fas fa-users-slash fa-2x mb-2"></i>
                <p>No members selected yet. Use the search above to add members.</p>
            </div>
        `;
    }
}

function removeMemberFromList(memberId) {
    const memberElement = document.querySelector(`[data-member-id="${memberId}"]`);
    if (memberElement) {
        memberElement.remove();
    }
}

function showAddMemberModal() {
    const churchId = document.getElementById('church_id').value;
    const coachId = document.getElementById('coach_id').value;
    
    if (!churchId || !coachId) {
        alert('Please select both Church and Coach first');
        return;
    }
    
    new bootstrap.Modal(document.getElementById('addMemberModal')).show();
}

function addNewMember() {
    const name = document.getElementById('newMemberName').value.trim();
    const email = document.getElementById('newMemberEmail').value.trim();
    const churchId = document.getElementById('church_id').value;
    const coachId = document.getElementById('coach_id').value;
    
         if (!name || !churchId || !coachId) {
         alert('Please fill in all required fields');
         return;
     }
    
    fetch('/events/satelife/addMemberToSatelife', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `name=${encodeURIComponent(name)}&email=${encodeURIComponent(email)}&church_id=${churchId}&coach_id=${coachId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert('Error: ' + data.error);
            return;
        }
        
        // Close modal
        bootstrap.Modal.getInstance(document.getElementById('addMemberModal')).hide();
        
        // Clear form
        document.getElementById('newMemberName').value = '';
        document.getElementById('newMemberEmail').value = '';
        
        // Add new member to the list
        if (data.member) {
            selectedMembers.add(data.member.id.toString());
            addMemberToList(data.member, false);
        }
        
        alert('Member added successfully!');
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adding member');
    });
}

// Handle Enter key in search
document.getElementById('memberSearch').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        searchMembers();
    }
});


</script>
