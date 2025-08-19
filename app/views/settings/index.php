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
				<button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createStatusModal">
					<i class="fas fa-plus me-1"></i>Add Status
				</button>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-hover align-middle">
						<thead>
							<tr>
								<th>Sort</th>
								<th>Name</th>
								<th>Slug</th>
								<th>Active</th>
								<th>Default</th>
								<th class="text-end">Actions</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($statuses as $status): ?>
							<tr>
								<td><span class="badge bg-light text-dark"><?= (int)$status['sort_order'] ?></span></td>
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
													<div class="col-md-4 mb-3">
														<label class="form-label">Sort Order</label>
														<input type="number" name="sort_order" class="form-control" value="<?= (int)$status['sort_order'] ?>">
													</div>
													<div class="col-md-4 mb-3 form-check mt-4">
														<input type="checkbox" name="is_active" class="form-check-input" id="active<?= (int)$status['id'] ?>" <?= $status['is_active'] ? 'checked' : '' ?>>
														<label class="form-check-label" for="active<?= (int)$status['id'] ?>">Active</label>
													</div>
													<div class="col-md-4 mb-3 form-check mt-4">
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
						<div class="col-md-4 mb-3">
							<label class="form-label">Sort Order</label>
							<input type="number" name="sort_order" class="form-control" value="0">
						</div>
						<div class="col-md-4 mb-3 form-check mt-4">
							<input type="checkbox" name="is_active" class="form-check-input" id="createActive" checked>
							<label class="form-check-label" for="createActive">Active</label>
						</div>
						<div class="col-md-4 mb-3 form-check mt-4">
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


