<?php

/**
 * DDoS Protection Configuration
 * 
 * This file contains configurable settings for the DDoS protection system.
 * Modify these values based on your server capacity and security requirements.
 */

return [
    // Rate Limiting Settings
    'rate_limit' => [
        'window_seconds' => 60,           // Time window for rate limiting (1 minute)
        'max_requests_per_window' => 100, // Maximum requests per IP per window
        'max_login_attempts' => 5,        // Maximum login attempts per IP per window
        'max_rapid_requests' => 50,       // Maximum requests in 5 minutes (suspicious activity)
    ],
    
    // IP Blocking Settings
    'ip_blocking' => [
        'block_duration_seconds' => 3600, // How long to block IPs (1 hour)
        'auto_unblock' => true,           // Automatically unblock IPs after duration
        'whitelist' => [                  // IPs that should never be blocked
            '127.0.0.1',                  // Localhost
            '::1',                        // IPv6 localhost
            // Add your server's IP addresses here
            // 'YOUR_SERVER_IP',
        ],
        'blacklist' => [                  // IPs that should always be blocked
            // Add known malicious IPs here
            // '192.168.1.100',
        ],
    ],
    
    // Request Validation
    'request_validation' => [
        'max_content_length' => 1048576,  // Maximum request size (1MB)
        'max_request_line' => 4096,       // Maximum request line length
        'max_request_fields' => 100,      // Maximum number of request fields
        'block_suspicious_headers' => true, // Block requests with suspicious headers
    ],
    
    // Suspicious Activity Detection
    'suspicious_detection' => [
        'check_user_agents' => true,      // Check for suspicious user agents
        'suspicious_patterns' => [        // Patterns that indicate suspicious activity
            '/bot/i', '/crawler/i', '/spider/i', '/scraper/i',
            '/curl/i', '/wget/i', '/python/i', '/java/i',
            '/masscan/i', '/nmap/i', '/sqlmap/i'
        ],
        'block_on_suspicious' => true,    // Block IPs on suspicious activity
    ],
    
    // Logging Settings
    'logging' => [
        'enabled' => true,                // Enable DDoS attempt logging
        'max_log_entries' => 1000,        // Maximum log entries to keep
        'log_suspicious_only' => false,   // Log only suspicious activities
        'log_file_path' => 'logs/ddos_attempts.log',
    ],
    
    // Monitoring and Alerts
    'monitoring' => [
        'enable_alerts' => false,         // Enable email/SMS alerts (requires setup)
        'alert_threshold' => 10,          // Number of blocked attempts before alert
        'alert_interval' => 300,          // Minimum seconds between alerts
    ],
    
    // Advanced Settings
    'advanced' => [
        'use_redis' => false,             // Use Redis for rate limiting (faster)
        'redis_host' => '127.0.0.1',     // Redis host
        'redis_port' => 6379,             // Redis port
        'redis_password' => null,         // Redis password
        'use_memcached' => false,         // Use Memcached for rate limiting
        'memcached_host' => '127.0.0.1', // Memcached host
        'memcached_port' => 11211,       // Memcached port
    ],
    
    // Cloudflare Integration (if using Cloudflare)
    'cloudflare' => [
        'enabled' => false,               // Enable Cloudflare integration
        'trust_proxy_headers' => true,    // Trust Cloudflare proxy headers
        'real_ip_header' => 'HTTP_CF_CONNECTING_IP', // Cloudflare real IP header
    ],
    
    // Geographic Blocking (optional)
    'geo_blocking' => [
        'enabled' => false,               // Enable geographic IP blocking
        'allowed_countries' => [          // Only allow these countries
            'US', 'CA', 'GB', 'AU', 'NZ'  // Example: US, Canada, UK, Australia, New Zealand
        ],
        'blocked_countries' => [          // Block these countries
            // Add countries to block
        ],
        'geoip_database' => 'GeoLite2-Country.mmdb', // MaxMind GeoIP database file
    ],
    
    // Performance Settings
    'performance' => [
        'cleanup_interval' => 3600,      // How often to clean old data (1 hour)
        'max_memory_usage' => '256M',    // Maximum memory usage for protection
        'use_file_locks' => true,        // Use file locks for thread safety
    ],
];
