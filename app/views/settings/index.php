<?php $layout = 'layouts/authenticated'; ?>

<div class="row">
	<div class="col-12 d-flex justify-content-between align-items-center mb-4">
		<h1 class="mb-0">
			<i class="fas fa-gear me-2"></i>System Settings
		</h1>
		<a href="/dashboard" class="btn btn-secondary">
			<i class="fas fa-arrow-left me-2"></i>Back
		</a>
	</div>
</div>

<div class="row">
	<div class="col-12">
		<div class="card mb-4" id="member-statuses">
			<div class="card-header d-flex justify-content-between align-items-center">
				<h5 class="mb-0">Member Statuses</h5>
				<div>
					<button class="btn btn-secondary btn-sm me-2" onclick="testSave()">
						<i class="fas fa-test me-1"></i>Test Save
					</button>
					<button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createStatusModal">
						<i class="fas fa-plus me-1"></i>Add Status
					</button>
				</div>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-hover align-middle" id="statusTable">
						<thead>
							<tr>
								<th style="width: 50px;">Sort</th>
								<th>Name</th>
								<th>Slug</th>
								<th>Active</th>
								<th>Default</th>
								<th class="text-end">Actions</th>
							</tr>
						</thead>
						<tbody id="statusTableBody">
							<?php foreach ($statuses as $status): ?>
							<tr data-id="<?= (int)$status['id'] ?>" data-sort-order="<?= (int)$status['sort_order'] ?>" class="status-row">
								<td>
									<div class="drag-handle" style="cursor: grab; padding: 5px; text-align: center; user-select: none;">
										<i class="fas fa-grip-vertical text-muted"></i>
									</div>
								</td>
								<td><?= htmlspecialchars($status['name']) ?></td>
								<td><code><?= htmlspecialchars($status['slug']) ?></code></td>
								<td>
									<span class="badge bg-<?= $status['is_active'] ? 'success' : 'secondary' ?>">
										<?= $status['is_active'] ? 'Yes' : 'No' ?>
									</span>
								</td>
								<td>
									<?= $status['is_default'] ? '<i class="fas fa-star text-warning"></i>' : '' ?>
								</td>
								<td class="text-end">
									<button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editStatusModal<?= (int)$status['id'] ?>">
										<i class="fas fa-edit"></i>
									</button>
									<form action="/settings/status/delete/<?= (int)$status['id'] ?>" method="POST" class="d-inline" onsubmit="return confirm('Delete this status?');">
										<button class="btn btn-sm btn-outline-danger" type="submit">
											<i class="fas fa-trash"></i>
										</button>
									</form>
								</td>
							</tr>

							<!-- Edit Modal -->
							<div class="modal fade" id="editStatusModal<?= (int)$status['id'] ?>" tabindex="-1">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
											<h5 class="modal-title">Edit Status</h5>
											<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
										</div>
										<form action="/settings/status/edit/<?= (int)$status['id'] ?>" method="POST">
											<div class="modal-body">
												<div class="mb-3">
													<label class="form-label">Name *</label>
													<input type="text" name="name" class="form-control" value="<?= htmlspecialchars($status['name']) ?>" required>
												</div>
												<div class="mb-3">
													<label class="form-label">Slug *</label>
													<input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($status['slug']) ?>" required>
													<div class="form-text">Lowercase, no spaces. Example: active_member</div>
												</div>
												<div class="row">
													<div class="col-md-3 mb-3">
														<label class="form-label">Sort Order</label>
														<input type="number" name="sort_order" class="form-control" value="<?= (int)$status['sort_order'] ?>">
													</div>
													<div class="col-md-3 mb-3">
														<label class="form-label">Badge Class</label>
														<select name="badge_class" class="form-select">
															<option value="">Default</option>
															<option value="success" <?= ($status['badge_class'] ?? '') === 'success' ? 'selected' : '' ?>>Success (Green)</option>
															<option value="secondary" <?= ($status['badge_class'] ?? '') === 'secondary' ? 'selected' : '' ?>>Secondary (Gray)</option>
															<option value="warning" <?= ($status['badge_class'] ?? '') === 'warning' ? 'selected' : '' ?>>Warning (Yellow)</option>
															<option value="danger" <?= ($status['badge_class'] ?? '') === 'danger' ? 'selected' : '' ?>>Danger (Red)</option>
															<option value="info" <?= ($status['badge_class'] ?? '') === 'info' ? 'selected' : '' ?>>Info (Blue)</option>
															<option value="primary" <?= ($status['badge_class'] ?? '') === 'primary' ? 'selected' : '' ?>>Primary (Purple)</option>
														</select>
													</div>
													<div class="col-md-3 mb-3 form-check mt-4">
														<input type="checkbox" name="is_active" class="form-check-input" id="active<?= (int)$status['id'] ?>" <?= $status['is_active'] ? 'checked' : '' ?>>
														<label class="form-check-label" for="active<?= (int)$status['id'] ?>">Active</label>
													</div>
													<div class="col-md-3 mb-3 form-check mt-4">
														<input type="checkbox" name="is_default" class="form-check-input" id="default<?= (int)$status['id'] ?>" <?= $status['is_default'] ? 'checked' : '' ?>>
														<label class="form-check-label" for="default<?= (int)$status['id'] ?>">Default</label>
													</div>
												</div>
											</div>
											<div class="modal-footer">
												<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
												<button type="submit" class="btn btn-primary">Save Changes</button>
											</div>
										</form>
									</div>
								</div>
							</div>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createStatusModal" tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Add Member Status</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>
			<form action="/settings/status/create" method="POST">
				<div class="modal-body">
					<div class="mb-3">
						<label class="form-label">Name *</label>
						<input type="text" name="name" class="form-control" required>
					</div>
					<div class="mb-3">
						<label class="form-label">Slug *</label>
						<input type="text" name="slug" class="form-control" placeholder="active" required>
						<div class="form-text">Lowercase, no spaces. Example: active</div>
					</div>
					<div class="row">
						<div class="col-md-3 mb-3">
							<label class="form-label">Sort Order</label>
							<input type="number" name="sort_order" class="form-control" value="0">
						</div>
						<div class="col-md-3 mb-3">
							<label class="form-label">Badge Class</label>
							<select name="badge_class" class="form-select">
								<option value="">Default</option>
								<option value="success">Success (Green)</option>
								<option value="secondary">Secondary (Gray)</option>
								<option value="warning">Warning (Yellow)</option>
								<option value="danger">Danger (Red)</option>
								<option value="info">Info (Blue)</option>
								<option value="primary">Primary (Purple)</option>
							</select>
						</div>
						<div class="col-md-3 mb-3 form-check mt-4">
							<input type="checkbox" name="is_active" class="form-check-input" id="createActive" checked>
							<label class="form-check-label" for="createActive">Active</label>
						</div>
						<div class="col-md-3 mb-3 form-check mt-4">
							<input type="checkbox" name="is_default" class="form-check-input" id="createDefault">
							<label class="form-check-label" for="createDefault">Default</label>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
					<button type="submit" class="btn btn-primary">Create</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tbody = document.getElementById('statusTableBody');
    let draggedElement = null;
    let originalOrder = [];

    console.log('Drag and drop script loaded');

    // Initialize sortable
    function initSortable() {
        const rows = tbody.querySelectorAll('.status-row');
        rows.forEach((row, index) => {
            row.setAttribute('data-sort-order', index + 1);
        });
        console.log('Initialized sortable with', rows.length, 'rows');
    }

    // Save original order
    function saveOriginalOrder() {
        originalOrder = Array.from(tbody.querySelectorAll('.status-row')).map(row => ({
            id: row.getAttribute('data-id'),
            sort_order: parseInt(row.getAttribute('data-sort-order'))
        }));
        console.log('Saved original order:', originalOrder);
    }

    // Update sort order after drag
    function updateSortOrder() {
        const rows = tbody.querySelectorAll('.status-row');
        rows.forEach((row, index) => {
            row.setAttribute('data-sort-order', index + 1);
        });
    }

    // Send updated order to server
    function saveNewOrder() {
        const rows = tbody.querySelectorAll('.status-row');
        const newOrder = Array.from(rows).map((row, index) => ({
            id: row.getAttribute('data-id'),
            sort_order: index + 1
        }));

        console.log('Saving new order:', newOrder);

        fetch('/settings/status/sort', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ statuses: newOrder })
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                // Show success message
                showNotification('Sort order updated successfully!', 'success');
            } else {
                // Revert to original order on error
                revertToOriginalOrder();
                showNotification('Failed to update sort order. Please try again.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            revertToOriginalOrder();
            showNotification('Failed to update sort order. Please try again.', 'error');
        });
    }

    // Revert to original order
    function revertToOriginalOrder() {
        originalOrder.forEach(item => {
            const row = tbody.querySelector(`[data-id="${item.id}"]`);
            if (row) {
                row.setAttribute('data-sort-order', item.sort_order);
            }
        });
        // Re-sort the table
        const rows = Array.from(tbody.querySelectorAll('.status-row'));
        rows.sort((a, b) => {
            const orderA = parseInt(a.getAttribute('data-sort-order'));
            const orderB = parseInt(b.getAttribute('data-sort-order'));
            return orderA - orderB;
        });
        rows.forEach(row => tbody.appendChild(row));
    }

    // Show notification
    function showNotification(message, type) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alert = document.createElement('div');
        alert.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
        alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alert);
        
        // Auto-remove after 3 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 3000);
    }

    // Simplified drag and drop implementation

    // Make all rows draggable
    tbody.querySelectorAll('.status-row').forEach(row => {
        row.draggable = true;
        console.log('Made row draggable:', row.getAttribute('data-id'));
        
        // Add visual feedback on hover
        row.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f8f9fa';
        });
        
        row.addEventListener('mouseleave', function() {
            if (!this.classList.contains('dragging')) {
                this.style.backgroundColor = '';
            }
        });
        

    });

    tbody.addEventListener('dragstart', function(e) {
        console.log('Drag start event triggered');
        const row = e.target.closest('.status-row');
        if (row) {
            draggedElement = row;
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/html', row.outerHTML);
            row.style.opacity = '0.5';
            row.classList.add('dragging');
            saveOriginalOrder();
            console.log('Started dragging row:', row.getAttribute('data-id'));
        }
    });

    tbody.addEventListener('dragend', function(e) {
        console.log('Drag end event');
        if (draggedElement) {
            draggedElement.style.opacity = '';
            draggedElement.classList.remove('dragging');
            draggedElement = null;
        }
    });

    tbody.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        
        const targetRow = e.target.closest('.status-row');
        if (targetRow && targetRow !== draggedElement) {
            const rect = targetRow.getBoundingClientRect();
            const midpoint = rect.top + rect.height / 2;
            
            // Remove existing drop indicators
            tbody.querySelectorAll('.status-row').forEach(row => {
                row.classList.remove('drop-above', 'drop-below');
            });
            
            if (e.clientY < midpoint) {
                targetRow.classList.add('drop-above');
            } else {
                targetRow.classList.add('drop-below');
            }
        }
    });

    tbody.addEventListener('dragleave', function(e) {
        if (!e.target.closest('.status-row')) {
            tbody.querySelectorAll('.status-row').forEach(row => {
                row.classList.remove('drop-above', 'drop-below');
            });
        }
    });

    tbody.addEventListener('drop', function(e) {
        e.preventDefault();
        console.log('Drop event triggered');
        
        const targetRow = e.target.closest('.status-row');
        if (targetRow && draggedElement && targetRow !== draggedElement) {
            console.log('Valid drop target found');
            const rect = targetRow.getBoundingClientRect();
            const midpoint = rect.top + rect.height / 2;
            
            if (e.clientY < midpoint) {
                // Insert before target
                targetRow.parentNode.insertBefore(draggedElement, targetRow);
                console.log('Inserted before target');
            } else {
                // Insert after target
                targetRow.parentNode.insertBefore(draggedElement, targetRow.nextSibling);
                console.log('Inserted after target');
            }
            
            updateSortOrder();
            saveNewOrder();
        }
        
        // Remove drop indicators
        tbody.querySelectorAll('.status-row').forEach(row => {
            row.classList.remove('drop-above', 'drop-below');
        });
    });

    // Initialize
    initSortable();
});

// Test function to manually trigger save
function testSave() {
    console.log('Test save function called');
    const tbody = document.getElementById('statusTableBody');
    const rows = tbody.querySelectorAll('.status-row');
    const newOrder = Array.from(rows).map((row, index) => ({
        id: row.getAttribute('data-id'),
        sort_order: index + 1
    }));

    console.log('Test order:', newOrder);

    fetch('/settings/status/sort', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ statuses: newOrder })
    })
    .then(response => {
        console.log('Test response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Test response data:', data);
        alert('Test save result: ' + JSON.stringify(data));
    })
    .catch(error => {
        console.error('Test error:', error);
        alert('Test save error: ' + error.message);
    });
}
</script>

<style>
.status-row {
    transition: all 0.2s ease;
}

.status-row.drop-above {
    border-top: 3px solid var(--primary-purple);
}

.status-row.drop-below {
    border-bottom: 3px solid var(--primary-purple);
}

.drag-handle:hover {
    color: var(--primary-purple) !important;
}

.status-row:active {
    cursor: grabbing;
}

.status-row.dragging {
    opacity: 0.5;
}
</style>