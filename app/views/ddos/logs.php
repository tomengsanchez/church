<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">DDoS Protection Logs</h1>
                <div>
                    <a href="/ddos-protection" class="btn btn-secondary">Back to Dashboard</a>
                    <form method="POST" action="/ddos-protection/clear-logs" class="d-inline" 
                          onsubmit="return confirm('Are you sure you want to clear all logs? This action cannot be undone.')">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Clear Logs
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent DDoS Attempts (Last 100 entries)</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($logs)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-info-circle fa-3x text-info mb-3"></i>
                            <p class="text-muted">No DDoS attempts have been logged yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Timestamp</th>
                                        <th>IP Address</th>
                                        <th>Type</th>
                                        <th>Details</th>
                                        <th>URI</th>
                                        <th>Severity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($logs as $log): ?>
                                        <tr class="<?= $this->getLogRowClass($log['type']) ?>">
                                            <td>
                                                <small><?= htmlspecialchars($log['timestamp']) ?></small>
                                            </td>
                                            <td>
                                                <code><?= htmlspecialchars($log['ip']) ?></code>
                                            </td>
                                            <td>
                                                <span class="badge <?= $this->getLogBadgeClass($log['type']) ?>">
                                                    <?= htmlspecialchars($log['type']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-muted">
                                                    <?= htmlspecialchars($log['details']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <code class="small"><?= htmlspecialchars($log['uri']) ?></code>
                                            </td>
                                            <td>
                                                <?= $this->getSeverityIcon($log['type']) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Log Type Legend -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Log Type Legend</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <h6><i class="fas fa-ban text-danger"></i> High Severity</h6>
                            <ul class="list-unstyled small">
                                <li>• <span class="badge bg-danger">RATE_LIMIT_EXCEEDED</span></li>
                                <li>• <span class="badge bg-danger">SUSPICIOUS_ACTIVITY</span></li>
                                <li>• <span class="badge bg-danger">BLOCKED_IP_ACCESS</span></li>
                            </ul>
                        </div>
                        <div class="col-md-3">
                            <h6><i class="fas fa-exclamation-triangle text-warning"></i> Medium Severity</h6>
                            <ul class="list-unstyled small">
                                <li>• <span class="badge bg-warning">INVALID_REQUEST</span></li>
                                <li>• <span class="badge bg-warning">SUSPICIOUS_USER_AGENT</span></li>
                            </ul>
                        </div>
                        <div class="col-md-3">
                            <h6><i class="fas fa-info-circle text-info"></i> Low Severity</h6>
                            <ul class="list-unstyled small">
                                <li>• <span class="badge bg-info">MONITORING</span></li>
                            </ul>
                        </div>
                        <div class="col-md-3">
                            <h6><i class="fas fa-clock text-secondary"></i> Time Windows</h6>
                            <ul class="list-unstyled small">
                                <li>• Rate Limit: 1 minute</li>
                                <li>• Suspicious Activity: 5 minutes</li>
                                <li>• IP Block: 1 hour</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Helper methods for the view
function getLogRowClass($type) {
    switch ($type) {
        case 'RATE_LIMIT_EXCEEDED':
        case 'SUSPICIOUS_ACTIVITY':
        case 'BLOCKED_IP_ACCESS':
            return 'table-danger';
        case 'INVALID_REQUEST':
            return 'table-warning';
        default:
            return '';
    }
}

function getLogBadgeClass($type) {
    switch ($type) {
        case 'RATE_LIMIT_EXCEEDED':
        case 'SUSPICIOUS_ACTIVITY':
        case 'BLOCKED_IP_ACCESS':
            return 'bg-danger';
        case 'INVALID_REQUEST':
            return 'bg-warning';
        case 'SUSPICIOUS_USER_AGENT':
            return 'bg-info';
        default:
            return 'bg-secondary';
    }
}

function getSeverityIcon($type) {
    switch ($type) {
        case 'RATE_LIMIT_EXCEEDED':
        case 'SUSPICIOUS_ACTIVITY':
        case 'BLOCKED_IP_ACCESS':
            return '<i class="fas fa-exclamation-triangle text-danger" title="High Severity"></i>';
        case 'INVALID_REQUEST':
            return '<i class="fas fa-exclamation-circle text-warning" title="Medium Severity"></i>';
        default:
            return '<i class="fas fa-info-circle text-info" title="Low Severity"></i>';
    }
}
?>
