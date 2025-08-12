<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\DdosProtection;

class DdosProtectionController extends Controller
{
    private DdosProtection $ddosProtection;
    
    public function __construct()
    {
        parent::__construct();
        $this->ddosProtection = new DdosProtection();
    }
    
    /**
     * Show DDoS protection dashboard
     */
    public function index(): void
    {
        $this->requireRole(ROLE_SUPER_ADMIN);
        
        $stats = $this->ddosProtection->getStats();
        $blockedIps = $this->getBlockedIps();
        
        $this->view('ddos/index', [
            'stats' => $stats,
            'blockedIps' => $blockedIps,
            'layout' => 'authenticated'
        ]);
    }
    
    /**
     * Unblock an IP address
     */
    public function unblockIp(): void
    {
        $this->requireRole(ROLE_SUPER_ADMIN);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ip = $_POST['ip'] ?? '';
            
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                $success = $this->ddosProtection->unblockIp($ip);
                
                if ($success) {
                    $_SESSION['flash'] = ['type' => 'success', 'message' => "IP $ip has been unblocked"];
                } else {
                    $_SESSION['flash'] = ['type' => 'error', 'message' => "Failed to unblock IP $ip"];
                }
            } else {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Invalid IP address'];
            }
            
            $this->redirect('/ddos-protection');
        }
    }
    
    /**
     * View DDoS protection logs
     */
    public function logs(): void
    {
        $this->requireRole(ROLE_SUPER_ADMIN);
        
        $logFile = __DIR__ . '/../../logs/ddos_attempts.log';
        $logs = [];
        
        if (file_exists($logFile)) {
            $logContent = file_get_contents($logFile);
            $logLines = array_filter(explode("\n", $logContent));
            
            // Get last 100 log entries
            $logLines = array_slice(array_reverse($logLines), 0, 100);
            
            foreach ($logLines as $line) {
                if (preg_match('/\[(.*?)\] (.*?) - (.*?): (.*?) - (.*)/', $line, $matches)) {
                    $logs[] = [
                        'timestamp' => $matches[1],
                        'ip' => $matches[2],
                        'type' => $matches[3],
                        'details' => $matches[4],
                        'uri' => $matches[5]
                    ];
                }
            }
        }
        
        $this->view('ddos/logs', [
            'logs' => $logs,
            'layout' => 'authenticated'
        ]);
    }
    
    /**
     * Get blocked IPs with details
     */
    private function getBlockedIps(): array
    {
        $blockedIpsFile = __DIR__ . '/../../logs/blocked_ips.json';
        
        if (!file_exists($blockedIpsFile)) {
            return [];
        }
        
        $blockedIps = json_decode(file_get_contents($blockedIpsFile), true) ?: [];
        $formattedIps = [];
        
        foreach ($blockedIps as $ip => $data) {
            $formattedIps[] = [
                'ip' => $ip,
                'blocked_at' => date('Y-m-d H:i:s', $data['blocked_at']),
                'expires_at' => date('Y-m-d H:i:s', $data['expires_at']),
                'reason' => $data['reason'],
                'is_expired' => time() > $data['expires_at']
            ];
        }
        
        // Sort by blocked_at (newest first)
        usort($formattedIps, function($a, $b) {
            return strtotime($b['blocked_at']) - strtotime($a['blocked_at']);
        });
        
        return $formattedIps;
    }
    
    /**
     * Clear old logs
     */
    public function clearLogs(): void
    {
        $this->requireRole(ROLE_SUPER_ADMIN);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $logFile = __DIR__ . '/../../logs/ddos_attempts.log';
            
            if (file_exists($logFile)) {
                file_put_contents($logFile, '');
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'DDoS logs have been cleared'];
            }
            
            $this->redirect('/ddos-protection/logs');
        }
    }
}
