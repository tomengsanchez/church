<?php
$layout = 'layouts/authenticated';
$title = 'Error Log Details';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-exclamation-triangle text-danger me-2"></i>Error Log Details
        </h1>
        <div class="btn-group">
            <a href="/errorlog" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Back to Logs
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Log Entry Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Timestamp:</strong></td>
                                    <td><?= htmlspecialchars($log['timestamp']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Level:</strong></td>
                                    <td>
                                        <?php
                                        $levelClass = match($log['level']) {
                                            'emergency', 'alert', 'critical' => 'danger',
                                            'error' => 'danger',
                                            'warning' => 'warning',
                                            'notice' => 'info',
                                            'info' => 'info',
                                            'debug' => 'secondary',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge bg-<?= $levelClass ?>">
                                            <?= strtoupper($log['level']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>User ID:</strong></td>
                                    <td><?= htmlspecialchars($log['user_id']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>IP Address:</strong></td>
                                    <td><code><?= htmlspecialchars($log['ip_address']) ?></code></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Request Method:</strong></td>
                                    <td><code><?= htmlspecialchars($log['request_method']) ?></code></td>
                                </tr>
                                <tr>
                                    <td><strong>Request URI:</strong></td>
                                    <td><code><?= htmlspecialchars($log['request_uri']) ?></code></td>
                                </tr>
                                <tr>
                                    <td><strong>User Agent:</strong></td>
                                    <td>
                                        <small class="text-muted">
                                            <?= htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown') ?>
                                        </small>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label"><strong>Error Message:</strong></label>
                        <div class="alert alert-<?= $levelClass ?>">
                            <?= htmlspecialchars($log['message']) ?>
                        </div>
                    </div>

                    <?php if (!empty($log['context'])): ?>
                        <div class="mb-3">
                            <label class="form-label"><strong>Context:</strong></label>
                            <pre class="bg-light p-3 rounded"><code><?= htmlspecialchars(json_encode($log['context'], JSON_PRETTY_PRINT)) ?></code></pre>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label"><strong>Raw Log Entry:</strong></label>
                        <pre class="bg-light p-3 rounded"><code><?= htmlspecialchars($log['raw']) ?></code></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 