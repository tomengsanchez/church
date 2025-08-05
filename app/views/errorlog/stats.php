<?php
$layout = 'layouts/authenticated';
$title = 'Error Log Statistics';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-bar text-info me-2"></i>Error Log Statistics
        </h1>
        <div class="btn-group">
            <a href="/errorlog" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Back to Logs
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Summary Cards -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Log Entries
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($stats['total_lines']) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Recent Errors (24h)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($stats['recent_errors']) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Log File Size
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= formatBytes($stats['file_size']) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Error Levels
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= count(array_filter($stats['by_level'], function($count) { return $count > 0; })) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Errors by Level -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Errors by Level</h6>
                </div>
                <div class="card-body">
                    <?php if (array_sum($stats['by_level']) > 0): ?>
                        <div class="chart-area">
                            <canvas id="errorLevelChart"></canvas>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-chart-pie text-muted fa-3x mb-3"></i>
                            <h5 class="text-muted">No Error Data</h5>
                            <p class="text-muted">No errors have been logged yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Error Level Breakdown -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Level Breakdown</h6>
                </div>
                <div class="card-body">
                    <?php if (array_sum($stats['by_level']) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Level</th>
                                        <th>Count</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $totalErrors = array_sum($stats['by_level']);
                                    foreach ($stats['by_level'] as $level => $count): 
                                        if ($count > 0):
                                            $percentage = ($count / $totalErrors) * 100;
                                    ?>
                                        <tr>
                                            <td>
                                                <?php
                                                $levelClass = match($level) {
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
                                                    <?= strtoupper($level) ?>
                                                </span>
                                            </td>
                                            <td><?= number_format($count) ?></td>
                                            <td><?= number_format($percentage, 1) ?>%</td>
                                        </tr>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-info-circle text-muted fa-2x mb-2"></i>
                            <p class="text-muted mb-0">No error data available</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Error Activity</h6>
                </div>
                <div class="card-body">
                    <?php if ($stats['recent_errors'] > 0): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong><?= $stats['recent_errors'] ?></strong> errors have been logged in the last 24 hours.
                        </div>
                        <p class="text-muted">
                            This indicates recent activity that may require attention. 
                            Check the main error logs for detailed information.
                        </p>
                    <?php else: ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>No recent errors!</strong> The system has been running smoothly in the last 24 hours.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (array_sum($stats['by_level']) > 0): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('errorLevelChart').getContext('2d');
    
    const data = {
        labels: [
            <?php foreach ($stats['by_level'] as $level => $count): ?>
                <?php if ($count > 0): ?>
                    '<?= strtoupper($level) ?>',
                <?php endif; ?>
            <?php endforeach; ?>
        ],
        datasets: [{
            data: [
                <?php foreach ($stats['by_level'] as $level => $count): ?>
                    <?php if ($count > 0): ?>
                        <?= $count ?>,
                    <?php endif; ?>
                <?php endforeach; ?>
            ],
            backgroundColor: [
                '#e74a3b', // Emergency/Alert/Critical/Error
                '#e74a3b', // Emergency/Alert/Critical/Error
                '#e74a3b', // Emergency/Alert/Critical/Error
                '#e74a3b', // Emergency/Alert/Critical/Error
                '#f6c23e', // Warning
                '#36b9cc', // Notice/Info
                '#36b9cc', // Notice/Info
                '#858796'  // Debug
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    };

    new Chart(ctx, {
        type: 'doughnut',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>
<?php endif; ?>

<?php
function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}
?> 