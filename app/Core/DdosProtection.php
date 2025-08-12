<?php

namespace App\Core;

class DdosProtection
{
    private const RATE_LIMIT_WINDOW = 60; // 1 minute
    private const MAX_REQUESTS_PER_WINDOW = 100; // Max requests per minute per IP
    private const MAX_LOGIN_ATTEMPTS = 5; // Max login attempts per IP per window
    private const BLOCK_DURATION = 3600; // Block duration in seconds (1 hour)
    
    private string $logFile;
    private string $blockedIpsFile;
    private string $rateLimitFile;
    
    public function __construct()
    {
        $this->logFile = __DIR__ . '/../../logs/ddos_attempts.log';
        $this->blockedIpsFile = __DIR__ . '/../../logs/blocked_ips.json';
        $this->rateLimitFile = __DIR__ . '/../../logs/rate_limits.json';
        
        // Ensure log directory exists
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Main protection method - call this before processing any request
     */
    public function protect(): bool
    {
        $ip = $this->getClientIp();
        
        // Check if IP is blocked
        if ($this->isIpBlocked($ip)) {
            $this->logAttempt($ip, 'BLOCKED_IP_ACCESS', 'Attempted access from blocked IP');
            $this->sendBlockedResponse();
            return false;
        }
        
        // Check rate limiting
        if (!$this->checkRateLimit($ip)) {
            $this->logAttempt($ip, 'RATE_LIMIT_EXCEEDED', 'Rate limit exceeded');
            $this->blockIp($ip, 'Rate limit exceeded');
            $this->sendRateLimitResponse();
            return false;
        }
        
        // Check for suspicious patterns
        if ($this->detectSuspiciousActivity($ip)) {
            $this->logAttempt($ip, 'SUSPICIOUS_ACTIVITY', 'Suspicious activity detected');
            $this->blockIp($ip, 'Suspicious activity');
            $this->sendBlockedResponse();
            return false;
        }
        
        // Validate request
        if (!$this->validateRequest()) {
            $this->logAttempt($ip, 'INVALID_REQUEST', 'Invalid request format');
            $this->sendInvalidRequestResponse();
            return false;
        }
        
        return true;
    }
    
    /**
     * Get client IP address
     */
    private function getClientIp(): string
    {
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (isset($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
    
    /**
     * Check if IP is blocked
     */
    private function isIpBlocked(string $ip): bool
    {
        if (!file_exists($this->blockedIpsFile)) {
            return false;
        }
        
        $blockedIps = json_decode(file_get_contents($this->blockedIpsFile), true) ?: [];
        
        if (isset($blockedIps[$ip])) {
            $blockData = $blockedIps[$ip];
            
            // Check if block has expired
            if (time() > $blockData['expires_at']) {
                unset($blockedIps[$ip]);
                file_put_contents($this->blockedIpsFile, json_encode($blockedIps));
                return false;
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Check rate limiting
     */
    private function checkRateLimit(string $ip): bool
    {
        $currentTime = time();
        $windowStart = $currentTime - self::RATE_LIMIT_WINDOW;
        
        $rateLimits = $this->loadRateLimits();
        
        // Clean old entries
        $rateLimits = array_filter($rateLimits, function($entry) use ($windowStart) {
            return $entry['timestamp'] > $windowStart;
        });
        
        // Count requests for this IP in current window
        $ipRequests = array_filter($rateLimits, function($entry) use ($ip) {
            return $entry['ip'] === $ip;
        });
        
        $requestCount = count($ipRequests);
        
        // Check if limit exceeded
        if ($requestCount >= self::MAX_REQUESTS_PER_WINDOW) {
            return false;
        }
        
        // Add current request
        $rateLimits[] = [
            'ip' => $ip,
            'timestamp' => $currentTime,
            'uri' => $_SERVER['REQUEST_URI'] ?? '/',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET'
        ];
        
        $this->saveRateLimits($rateLimits);
        
        return true;
    }
    
    /**
     * Detect suspicious activity
     */
    private function detectSuspiciousActivity(string $ip): bool
    {
        $currentTime = time();
        $windowStart = $currentTime - 300; // 5 minutes
        
        $rateLimits = $this->loadRateLimits();
        $recentRequests = array_filter($rateLimits, function($entry) use ($windowStart, $ip) {
            return $entry['timestamp'] > $windowStart && $entry['ip'] === $ip;
        });
        
        // Check for rapid-fire requests (more than 50 requests in 5 minutes)
        if (count($recentRequests) > 50) {
            return true;
        }
        
        // Check for repeated failed login attempts
        $loginAttempts = array_filter($recentRequests, function($entry) {
            return strpos($entry['uri'], '/auth/login') !== false;
        });
        
        if (count($loginAttempts) > self::MAX_LOGIN_ATTEMPTS) {
            return true;
        }
        
        // Check for suspicious user agents
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $suspiciousPatterns = [
            '/bot/i', '/crawler/i', '/spider/i', '/scraper/i',
            '/curl/i', '/wget/i', '/python/i', '/java/i'
        ];
        
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                // Log but don't block immediately - just monitor
                $this->logAttempt($ip, 'SUSPICIOUS_USER_AGENT', "User agent: $userAgent");
            }
        }
        
        return false;
    }
    
    /**
     * Validate request format
     */
    private function validateRequest(): bool
    {
        // Check request size
        $contentLength = (int)($_SERVER['CONTENT_LENGTH'] ?? 0);
        if ($contentLength > 1048576) { // 1MB limit
            return false;
        }
        
        // Check for suspicious headers
        $suspiciousHeaders = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP'];
        foreach ($suspiciousHeaders as $header) {
            if (isset($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Block an IP address
     */
    private function blockIp(string $ip, string $reason): void
    {
        $blockedIps = [];
        if (file_exists($this->blockedIpsFile)) {
            $blockedIps = json_decode(file_get_contents($this->blockedIpsFile), true) ?: [];
        }
        
        $blockedIps[$ip] = [
            'blocked_at' => time(),
            'expires_at' => time() + self::BLOCK_DURATION,
            'reason' => $reason
        ];
        
        file_put_contents($this->blockedIpsFile, json_encode($blockedIps));
    }
    
    /**
     * Load rate limits from file
     */
    private function loadRateLimits(): array
    {
        if (!file_exists($this->rateLimitFile)) {
            return [];
        }
        
        return json_decode(file_get_contents($this->rateLimitFile), true) ?: [];
    }
    
    /**
     * Save rate limits to file
     */
    private function saveRateLimits(array $rateLimits): void
    {
        file_put_contents($this->rateLimitFile, json_encode($rateLimits));
    }
    
    /**
     * Log DDoS attempt
     */
    private function logAttempt(string $ip, string $type, string $details): void
    {
        $logEntry = sprintf(
            "[%s] %s - %s: %s - %s\n",
            date('Y-m-d H:i:s'),
            $ip,
            $type,
            $details,
            $_SERVER['REQUEST_URI'] ?? '/'
        );
        
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Send blocked response
     */
    private function sendBlockedResponse(): void
    {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Access denied', 'code' => 'IP_BLOCKED']);
        exit;
    }
    
    /**
     * Send rate limit response
     */
    private function sendRateLimitResponse(): void
    {
        http_response_code(429);
        header('Content-Type: application/json');
        header('Retry-After: ' . self::RATE_LIMIT_WINDOW);
        echo json_encode(['error' => 'Too many requests', 'code' => 'RATE_LIMIT_EXCEEDED']);
        exit;
    }
    
    /**
     * Send invalid request response
     */
    private function sendInvalidRequestResponse(): void
    {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid request', 'code' => 'INVALID_REQUEST']);
        exit;
    }
    
    /**
     * Get protection statistics
     */
    public function getStats(): array
    {
        $blockedIps = [];
        if (file_exists($this->blockedIpsFile)) {
            $blockedIps = json_decode(file_get_contents($this->blockedIpsFile), true) ?: [];
        }
        
        $rateLimits = $this->loadRateLimits();
        $currentTime = time();
        $windowStart = $currentTime - self::RATE_LIMIT_WINDOW;
        
        $recentRequests = array_filter($rateLimits, function($entry) use ($windowStart) {
            return $entry['timestamp'] > $windowStart;
        });
        
        return [
            'blocked_ips_count' => count($blockedIps),
            'requests_last_minute' => count($recentRequests),
            'total_requests_today' => count(array_filter($rateLimits, function($entry) use ($currentTime) {
                return $entry['timestamp'] > ($currentTime - 86400);
            }))
        ];
    }
    
    /**
     * Unblock an IP address
     */
    public function unblockIp(string $ip): bool
    {
        if (!file_exists($this->blockedIpsFile)) {
            return false;
        }
        
        $blockedIps = json_decode(file_get_contents($this->blockedIpsFile), true) ?: [];
        
        if (isset($blockedIps[$ip])) {
            unset($blockedIps[$ip]);
            file_put_contents($this->blockedIpsFile, json_encode($blockedIps));
            return true;
        }
        
        return false;
    }
}
