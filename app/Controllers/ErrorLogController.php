<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Logger;

class ErrorLogController extends Controller
{
    private Logger $logger;

    public function __construct()
    {
        $this->logger = new Logger();
    }

    public function index(): void
    {
        // Check permissions - only super admin can view error logs
        if (!$this->hasPermission(ROLE_SUPER_ADMIN)) {
            $this->redirect('/dashboard');
            return;
        }

        $page = (int)($_GET['page'] ?? 1);
        $perPage = 50;
        $level = $_GET['level'] ?? '';
        $search = $_GET['search'] ?? '';

        // Get logs from file
        $logs = $this->logger->getLogs(1000, $level ?: null);
        
        // Apply search filter if provided
        if (!empty($search)) {
            $logs = array_filter($logs, function($log) use ($search) {
                return stripos($log['message'], $search) !== false || 
                       stripos($log['raw'], $search) !== false;
            });
        }

        // Calculate pagination
        $totalLogs = count($logs);
        $totalPages = ceil($totalLogs / $perPage);
        $offset = ($page - 1) * $perPage;
        
        // Get paginated logs
        $paginatedLogs = array_slice($logs, $offset, $perPage);

        $pagination = [
            'current_page' => $page,
            'per_page' => $perPage,
            'total_records' => $totalLogs,
            'total_pages' => $totalPages
        ];

        $filters = [
            'level' => $level,
            'search' => $search
        ];

        $errorLevels = $this->logger->getLogLevels();

        $this->view('errorlog/index', [
            'logs' => $paginatedLogs,
            'pagination' => $pagination,
            'filters' => $filters,
            'errorLevels' => $errorLevels
        ]);
    }

    public function view(int $id): void
    {
        // Check permissions
        if (!$this->hasPermission(ROLE_SUPER_ADMIN)) {
            $this->redirect('/dashboard');
            return;
        }

        // For file-based logging, we'll use the line number as ID
        $logs = $this->logger->getLogs(1000);
        
        if (!isset($logs[$id])) {
            setFlash('error', 'Error log not found.');
            $this->redirect('/errorlog');
            return;
        }

        $log = $logs[$id];

        $this->view('errorlog/view', [
            'log' => $log
        ]);
    }

    public function stats(): void
    {
        // Check permissions
        if (!$this->hasPermission(ROLE_SUPER_ADMIN)) {
            $this->redirect('/dashboard');
            return;
        }

        $stats = $this->logger->getLogStats();
        $errorLevels = $this->logger->getLogLevels();

        $this->view('errorlog/stats', [
            'stats' => $stats,
            'errorLevels' => $errorLevels
        ]);
    }

    public function delete(int $id): void
    {
        // Check permissions
        if (!$this->hasPermission(ROLE_SUPER_ADMIN)) {
            $this->redirect('/dashboard');
            return;
        }

        // For file-based logging, we can't delete individual lines easily
        // Instead, we'll clear all logs
        if ($this->logger->clearLogs()) {
            setFlash('success', 'Error logs cleared successfully.');
        } else {
            setFlash('error', 'Failed to clear error logs.');
        }

        $this->redirect('/errorlog');
    }

    public function clearOld(): void
    {
        // Check permissions
        if (!$this->hasPermission(ROLE_SUPER_ADMIN)) {
            $this->redirect('/dashboard');
            return;
        }

        $daysOld = (int)($_POST['days_old'] ?? 30);
        $deletedCount = $this->logger->clearOldLogs($daysOld);

        setFlash('success', "Deleted {$deletedCount} old log backup files (older than {$daysOld} days).");
        $this->redirect('/errorlog');
    }

    public function clearAll(): void
    {
        // Check permissions
        if (!$this->hasPermission(ROLE_SUPER_ADMIN)) {
            $this->redirect('/dashboard');
            return;
        }

        if ($this->logger->clearLogs()) {
            setFlash('success', 'All error logs cleared successfully.');
        } else {
            setFlash('error', 'Failed to clear error logs.');
        }
        
        $this->redirect('/errorlog');
    }

    public function export(): void
    {
        // Check permissions
        if (!$this->hasPermission(ROLE_SUPER_ADMIN)) {
            $this->redirect('/dashboard');
            return;
        }

        $level = $_GET['level'] ?? '';
        $search = $_GET['search'] ?? '';

        // Get all logs for export
        $logs = $this->logger->getLogs(10000, $level ?: null);
        
        // Apply search filter if provided
        if (!empty($search)) {
            $logs = array_filter($logs, function($log) use ($search) {
                return stripos($log['message'], $search) !== false || 
                       stripos($log['raw'], $search) !== false;
            });
        }

        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="error_logs_' . date('Y-m-d_H-i-s') . '.csv"');

        $output = fopen('php://output', 'w');

        // CSV headers
        fputcsv($output, ['Timestamp', 'Level', 'User ID', 'IP Address', 'Request Method', 'Request URI', 'Message', 'Context']);

        // CSV data
        foreach ($logs as $log) {
            fputcsv($output, [
                $log['timestamp'],
                $log['level'],
                $log['user_id'],
                $log['ip_address'],
                $log['request_method'],
                $log['request_uri'],
                $log['message'],
                json_encode($log['context'])
            ]);
        }

        fclose($output);
        exit;
    }
} 