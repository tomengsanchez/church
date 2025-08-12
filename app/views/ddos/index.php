<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">DDoS Protection Dashboard</h1>
                <div>
                    <a href="/ddos-protection/logs" class="btn btn-info">View Logs</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">Blocked IPs</h4>
                            <h2 class="mb-0"><?= $stats['blocked_ips_count'] ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-shield-alt fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">Requests (Last Min)</h4>
                            <h2 class="mb-0"><?= $stats['requests_last_minute'] ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chart-line fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">Total Requests (Today)</h4>
                            <h2 class="mb-0"><?= $stats['total_requests_today'] ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-day fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Blocked IPs Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Blocked IP Addresses</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($blockedIps)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <p class="text-muted">No IP addresses are currently blocked.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>IP Address</th>
                                        <th>Blocked At</th>
                                        <th>Expires At</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($blockedIps as $ip): ?>
                                        <tr class="<?= $ip['is_expired'] ? 'table-warning' : '' ?>">
                                            <td>
                                                <code><?= htmlspecialchars($ip['ip']) ?></code>
                                            </td>
                                            <td><?= htmlspecialchars($ip['blocked_at']) ?></td>
                                            <td><?= htmlspecialchars($ip['expires_at']) ?></td>
                                            <td>
                                                <span class="badge bg-danger">
                                                    <?= htmlspecialchars($ip['reason']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($ip['is_expired']): ?>
                                                    <span class="badge bg-warning">Expired</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Active</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!$ip['is_expired']): ?>
                                                    <form method="POST" action="/ddos-protection/unblock-ip" class="d-inline">
                                                        <input type="hidden" name="ip" value="<?= htmlspecialchars($ip['ip']) ?>">
                                                        <button type="submit" class="btn btn-sm btn-success" 
                                                                onclick="return confirm('Are you sure you want to unblock this IP?')">
                                                            <i class="fas fa-unlock"></i> Unblock
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="text-muted">Auto-expired</span>
                                                <?php endif; ?>
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

    <!-- Protection Information -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Protection Features</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-shield-alt text-primary"></i> Rate Limiting</h6>
                            <ul class="list-unstyled">
                                <li>• Maximum 100 requests per minute per IP</li>
                                <li>• Maximum 5 login attempts per IP per window</li>
                                <li>• Automatic IP blocking on violation</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-eye text-info"></i> Monitoring</h6>
                            <ul class="list-unstyled">
                                <li>• Real-time request tracking</li>
                                <li>• Suspicious activity detection</li>
                                <li>• Comprehensive logging system</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
