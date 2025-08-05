<?php

namespace App\Core;

class Logger
{
    private string $logFile;
    private string $logDir;

    public function __construct(string $logFile = 'error.log')
    {
        $this->logDir = __DIR__ . '/../../logs';
        $this->logFile = $logFile;
        
        // Create logs directory if it doesn't exist
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }

    public function log(string $level, string $message, array $context = []): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $userId = $_SESSION['user_id'] ?? 'guest';
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $requestUri = $_SERVER['REQUEST_URI'] ?? 'unknown';
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'unknown';

        $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
        
        $logEntry = sprintf(
            "[%s] [%s] [User: %s] [IP: %s] [%s %s] %s%s\n",
            $timestamp,
            strtoupper($level),
            $userId,
            $ipAddress,
            $requestMethod,
            $requestUri,
            $message,
            $contextStr
        );

        $filePath = $this->logDir . '/' . $this->logFile;
        
        // Rotate log file if it's too large (10MB)
        if (file_exists($filePath) && filesize($filePath) > 10 * 1024 * 1024) {
            $this->rotateLog();
        }

        file_put_contents($filePath, $logEntry, FILE_APPEND | LOCK_EX);
    }

    public function emergency(string $message, array $context = []): void
    {
        $this->log('emergency', $message, $context);
    }

    public function alert(string $message, array $context = []): void
    {
        $this->log('alert', $message, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    public function notice(string $message, array $context = []): void
    {
        $this->log('notice', $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    private function rotateLog(): void
    {
        $filePath = $this->logDir . '/' . $this->logFile;
        $backupFile = $filePath . '.' . date('Y-m-d_H-i-s') . '.bak';
        
        if (file_exists($filePath)) {
            rename($filePath, $backupFile);
        }
    }

    public function getLogs(int $lines = 100, string $level = null): array
    {
        $filePath = $this->logDir . '/' . $this->logFile;
        
        if (!file_exists($filePath)) {
            return [];
        }

        $logs = [];
        $file = new \SplFileObject($filePath);
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key();
        
        $startLine = max(0, $totalLines - $lines);
        $file->seek($startLine);
        
        while (!$file->eof()) {
            $line = trim($file->current());
            if (!empty($line)) {
                if ($level === null || stripos($line, "[$level]") !== false) {
                    $logs[] = $this->parseLogLine($line);
                }
            }
            $file->next();
        }

        return array_reverse($logs);
    }

    public function getLogStats(): array
    {
        $filePath = $this->logDir . '/' . $this->logFile;
        
        if (!file_exists($filePath)) {
            return [
                'total_lines' => 0,
                'file_size' => 0,
                'by_level' => [],
                'recent_errors' => 0
            ];
        }

        $stats = [
            'total_lines' => 0,
            'file_size' => filesize($filePath),
            'by_level' => [],
            'recent_errors' => 0
        ];

        $levels = ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'];
        foreach ($levels as $level) {
            $stats['by_level'][$level] = 0;
        }

        $file = new \SplFileObject($filePath);
        $oneDayAgo = strtotime('-1 day');

        while (!$file->eof()) {
            $line = trim($file->current());
            if (!empty($line)) {
                $stats['total_lines']++;
                
                // Parse timestamp to check if recent
                if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $line, $matches)) {
                    $timestamp = strtotime($matches[1]);
                    if ($timestamp >= $oneDayAgo) {
                        $stats['recent_errors']++;
                    }
                }

                // Count by level
                foreach ($levels as $level) {
                    if (stripos($line, "[$level]") !== false) {
                        $stats['by_level'][$level]++;
                        break;
                    }
                }
            }
            $file->next();
        }

        return $stats;
    }

    public function clearLogs(): bool
    {
        $filePath = $this->logDir . '/' . $this->logFile;
        
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        
        return true;
    }

    public function clearOldLogs(int $daysOld = 30): int
    {
        $deletedCount = 0;
        $cutoffTime = strtotime("-$daysOld days");
        
        $files = glob($this->logDir . '/*.log.*.bak');
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime) {
                if (unlink($file)) {
                    $deletedCount++;
                }
            }
        }
        
        return $deletedCount;
    }

    private function parseLogLine(string $line): array
    {
        $parsed = [
            'raw' => $line,
            'timestamp' => '',
            'level' => '',
            'user_id' => '',
            'ip_address' => '',
            'request_method' => '',
            'request_uri' => '',
            'message' => '',
            'context' => []
        ];

        // Extract timestamp
        if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $line, $matches)) {
            $parsed['timestamp'] = $matches[1];
        }

        // Extract level
        if (preg_match('/\[([A-Z]+)\]/', $line, $matches)) {
            $parsed['level'] = strtolower($matches[1]);
        }

        // Extract user ID
        if (preg_match('/\[User: ([^\]]+)\]/', $line, $matches)) {
            $parsed['user_id'] = $matches[1];
        }

        // Extract IP address
        if (preg_match('/\[IP: ([^\]]+)\]/', $line, $matches)) {
            $parsed['ip_address'] = $matches[1];
        }

        // Extract request method and URI
        if (preg_match('/\[([A-Z]+) ([^\]]+)\]/', $line, $matches)) {
            $parsed['request_method'] = $matches[1];
            $parsed['request_uri'] = $matches[2];
        }

        // Extract message and context
        $parts = explode(' | Context: ', $line);
        $messagePart = end($parts);
        
        if (strpos($messagePart, ' | Context: ') !== false) {
            $messageParts = explode(' | Context: ', $messagePart);
            $parsed['message'] = trim($messageParts[0]);
            if (isset($messageParts[1])) {
                $parsed['context'] = json_decode($messageParts[1], true) ?: [];
            }
        } else {
            $parsed['message'] = trim($messagePart);
        }

        return $parsed;
    }

    public function getLogLevels(): array
    {
        return [
            'emergency' => 'Emergency',
            'alert' => 'Alert',
            'critical' => 'Critical',
            'error' => 'Error',
            'warning' => 'Warning',
            'notice' => 'Notice',
            'info' => 'Info',
            'debug' => 'Debug'
        ];
    }
} 