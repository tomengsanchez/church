<?php $layout = 'layouts/authenticated'; ?>

<div class="row">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Edit Lifegroup Event</h5>
                <a href="/events/lifegroup" class="btn btn-sm btn-outline-secondary">Back</a>
            </div>
            <div class="card-body">
                <form action="/events/lifegroup/edit/<?= (int)$event['id'] ?>" method="post">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($event['title']) ?>" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="event_date" class="form-control" value="<?= htmlspecialchars($event['event_date']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Time</label>
                            <?php
                            $timeOptions = [];
                            for ($h = 1; $h <= 11; $h++) {
                                $value = sprintf('%02d:00:00', $h);
                                $label = date('g:i A', strtotime($value));
                                $timeOptions[] = ['value' => $value, 'label' => $label];
                            }
                            // Add 12:00 PM
                            $timeOptions[] = ['value' => '12:00:00', 'label' => '12:00 PM'];
                            // Add 1:00 PM to 11:00 PM
                            for ($h = 13; $h <= 23; $h++) {
                                $value = sprintf('%02d:00:00', $h);
                                $label = date('g:i A', strtotime($value));
                                $timeOptions[] = ['value' => $value, 'label' => $label];
                            }
                            $selectedTime = $event['event_time'] ?? '';
                            ?>
                            <select name="event_time" class="form-select">
                                <option value="">Select Time</option>
                                <?php foreach ($timeOptions as $opt): ?>
                                    <option value="<?= $opt['value'] ?>" <?= $selectedTime === $opt['value'] ? 'selected' : '' ?>><?= $opt['label'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($event['location']) ?>" placeholder="e.g., Conference Room">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Church</label>
                        <select name="church_id" class="form-select" required>
                            <?php foreach ($churches as $church): ?>
                                <option value="<?= (int)$church['id'] ?>" <?= (int)$church['id'] === (int)$event['church_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($church['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if (!empty($lifegroups)): ?>
                    <div class="mb-3">
                        <label class="form-label">Lifegroup</label>
                        <select name="lifegroup_id" class="form-select" id="lifegroup-select">
                            <option value="">Select Lifegroup</option>
                            <?php 
                                $selectedLifegroupId = $selectedLifegroupId ?? ($event['lifegroup_id'] ?? null);
                                if (empty($selectedLifegroupId) && !empty($lifegroups) && isset($lifegroups[0]['id'])) {
                                    $selectedLifegroupId = (int)$lifegroups[0]['id'];
                                }
                            ?>
                            <?php foreach ($lifegroups as $lg): ?>
                                <option value="<?= (int)$lg['id'] ?>" <?= isset($selectedLifegroupId) && (int)$lg['id'] === (int)$selectedLifegroupId ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($lg['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Options are filtered based on your role and assignments.</small>
                    </div>
                    <?php endif; ?>
                    <div class="mb-3" id="attendance-container" style="display:none;">
                        <label class="form-label">Attendance</label>
                        <div id="attendance-list" class="border rounded p-2" style="max-height: 220px; overflow:auto;"></div>
                        <small class="text-muted">Check members who attended.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Optional"><?= htmlspecialchars($event['description']) ?></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                        <a href="/events/lifegroup" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const lgSelect = document.getElementById('lifegroup-select');
  const container = document.getElementById('attendance-container');
  const list = document.getElementById('attendance-list');

  async function loadMembers(lgId, eventId) {
    if (!lgId) {
      container.style.display = 'none';
      list.innerHTML = '';
      return;
    }
    try {
      const url = eventId ? `/events/lifegroup/members/${lgId}?eventId=${eventId}` : `/events/lifegroup/members/${lgId}`;
      const res = await fetch(url);
      const data = await res.json();
      list.innerHTML = '';
      (data.members || []).forEach(m => {
        const id = `att_${m.id}`;
        const wrapper = document.createElement('div');
        wrapper.className = 'form-check';
        const checked = m.checked ? 'checked' : '';
        wrapper.innerHTML = `<input class="form-check-input" type="checkbox" name="attendees[]" value="${m.id}" id="${id}" ${checked}>` +
                            `<label class="form-check-label" for="${id}">${m.name}</label>`;
        list.appendChild(wrapper);
      });
      container.style.display = (data.members || []).length ? 'block' : 'none';
    } catch (e) {
      container.style.display = 'none';
      list.innerHTML = '';
    }
  }

  if (lgSelect) {
    lgSelect.addEventListener('change', (e) => loadMembers(e.target.value, <?= (int)$event['id'] ?>));
    const preset = lgSelect.value || '';
    if (preset) loadMembers(preset, <?= (int)$event['id'] ?>);
  }
});
</script>
