# DDoS Protection System for Church App

This document describes the comprehensive DDoS protection system implemented in your church application.

## 🛡️ Overview

The DDoS protection system provides multiple layers of security to protect your application from various types of attacks:

- **Rate Limiting**: Prevents excessive requests from single IPs
- **IP Blocking**: Automatically blocks malicious IPs
- **Request Validation**: Validates request format and content
- **Suspicious Activity Detection**: Identifies and blocks suspicious behavior
- **Comprehensive Logging**: Tracks all security events
- **Admin Dashboard**: Web interface for monitoring and management

## 🚀 Features

### 1. Rate Limiting
- **Window-based**: 1-minute sliding window
- **Per-IP limits**: 100 requests per minute per IP
- **Login protection**: 5 login attempts per IP per window
- **Automatic blocking**: IPs are blocked when limits are exceeded

### 2. IP Blocking
- **Automatic blocking**: Based on rate limits and suspicious activity
- **Configurable duration**: 1-hour blocks (configurable)
- **Auto-expiration**: Blocks automatically expire
- **Manual management**: Admins can unblock IPs

### 3. Request Validation
- **Size limits**: 1MB maximum request size
- **Header validation**: Checks for suspicious headers
- **Format validation**: Ensures proper request structure
- **SQL injection protection**: Blocks suspicious query strings

### 4. Suspicious Activity Detection
- **User agent analysis**: Detects bots and scrapers
- **Pattern recognition**: Identifies attack patterns
- **Rapid request detection**: Blocks rapid-fire requests
- **Behavioral analysis**: Monitors request patterns

### 5. Logging and Monitoring
- **Real-time logging**: All security events are logged
- **Admin dashboard**: Web interface for monitoring
- **Statistics**: Request counts, blocked IPs, etc.
- **Export capabilities**: Log data can be exported

## 📁 File Structure

```
app/
├── Core/
│   └── DdosProtection.php          # Main protection class
├── Controllers/
│   └── DdosProtectionController.php # Admin controller
├── views/
│   └── ddos/
│       ├── index.php               # Dashboard view
│       └── logs.php                # Logs view
├── config/
│   └── ddos-config.php            # Configuration file
└── views/errors/
    └── 403.php                     # Access denied page

logs/                               # Log files directory
├── ddos_attempts.log              # DDoS attempt logs
├── blocked_ips.json               # Blocked IPs data
└── rate_limits.json               # Rate limiting data

.htaccess                          # Server-level protection
```

## ⚙️ Configuration

### Basic Settings

Edit `app/config/ddos-config.php` to customize protection levels:

```php
'rate_limit' => [
    'window_seconds' => 60,           // Time window (1 minute)
    'max_requests_per_window' => 100, // Max requests per IP
    'max_login_attempts' => 5,        // Max login attempts
],
```

### IP Whitelisting

Add trusted IPs to the whitelist:

```php
'ip_blocking' => [
    'whitelist' => [
        '127.0.0.1',                  // Localhost
        'YOUR_SERVER_IP',             // Your server IP
        'TRUSTED_IP_ADDRESS',         // Other trusted IPs
    ],
],
```

### Geographic Blocking

Enable country-based blocking:

```php
'geo_blocking' => [
    'enabled' => true,
    'allowed_countries' => ['US', 'CA', 'GB'],
    'blocked_countries' => ['XX', 'YY'],
],
```

## 🔧 Installation

### 1. Automatic Integration

The DDoS protection is automatically integrated into your application. No additional setup required.

### 2. Manual Integration (if needed)

```php
// In your main application file
use App\Core\DdosProtection;

$ddosProtection = new DdosProtection();
if (!$ddosProtection->protect()) {
    exit; // Request blocked
}
```

### 3. Server Requirements

- **Apache**: Enable mod_rewrite and mod_headers
- **PHP**: 7.4+ with file writing permissions
- **Storage**: At least 100MB for logs and data

## 📊 Admin Dashboard

### Access

Navigate to `/ddos-protection` (Super Admin only)

### Features

- **Statistics**: Real-time protection statistics
- **Blocked IPs**: View and manage blocked IP addresses
- **Logs**: View detailed security logs
- **Management**: Unblock IPs, clear logs

### Dashboard Sections

1. **Statistics Cards**
   - Blocked IPs count
   - Requests in last minute
   - Total requests today

2. **Blocked IPs Table**
   - IP address
   - Block reason and timestamp
   - Expiration time
   - Unblock actions

3. **Protection Information**
   - Current protection settings
   - Rate limiting details
   - Monitoring features

## 📝 Logging

### Log Types

- **RATE_LIMIT_EXCEEDED**: IP exceeded rate limits
- **SUSPICIOUS_ACTIVITY**: Suspicious behavior detected
- **BLOCKED_IP_ACCESS**: Attempted access from blocked IP
- **INVALID_REQUEST**: Malformed or suspicious request
- **SUSPICIOUS_USER_AGENT**: Suspicious user agent detected

### Log Format

```
[2024-01-15 14:30:25] 192.168.1.100 - RATE_LIMIT_EXCEEDED: Rate limit exceeded - /auth/login
```

### Log Management

- **Auto-cleanup**: Old entries are automatically removed
- **Manual clearing**: Admins can clear logs via dashboard
- **Export**: Logs can be exported for analysis

## 🚨 Monitoring and Alerts

### Real-time Monitoring

- **Live statistics**: Updated in real-time
- **Request tracking**: Monitor all incoming requests
- **Block monitoring**: Track blocked IPs and reasons

### Alert System

```php
'monitoring' => [
    'enable_alerts' => true,
    'alert_threshold' => 10,          // Alert after 10 blocks
    'alert_interval' => 300,          // Max 1 alert per 5 minutes
],
```

## 🔒 Security Features

### Request Validation

- **Content length**: Maximum 1MB per request
- **Header validation**: Checks for spoofed headers
- **Query string**: Blocks SQL injection attempts
- **File uploads**: Validates file types and sizes

### IP Protection

- **Proxy detection**: Handles Cloudflare and other proxies
- **Spoofing prevention**: Validates IP addresses
- **Range blocking**: Can block entire IP ranges
- **Geographic blocking**: Country-based restrictions

### Bot Protection

- **User agent analysis**: Detects automated tools
- **Behavioral analysis**: Identifies bot patterns
- **Rate limiting**: Prevents bot flooding
- **Pattern recognition**: Blocks known attack patterns

## 🚀 Performance Optimization

### File-based Storage

- **Lightweight**: No database dependencies
- **Fast**: File-based operations
- **Scalable**: Handles thousands of IPs

### Memory Management

- **Efficient cleanup**: Automatic data cleanup
- **Memory limits**: Configurable memory usage
- **File locking**: Thread-safe operations

### Advanced Options

```php
'advanced' => [
    'use_redis' => true,              // Use Redis for better performance
    'use_memcached' => false,         // Alternative to Redis
    'cleanup_interval' => 3600,       // Cleanup every hour
],
```

## 🛠️ Troubleshooting

### Common Issues

1. **Permission Errors**
   - Ensure `logs/` directory is writable
   - Check PHP file permissions

2. **Performance Issues**
   - Enable Redis/Memcached
   - Reduce log retention period
   - Optimize cleanup intervals

3. **False Positives**
   - Add legitimate IPs to whitelist
   - Adjust rate limiting thresholds
   - Review suspicious activity patterns

### Debug Mode

Enable detailed logging for troubleshooting:

```php
'logging' => [
    'enabled' => true,
    'log_suspicious_only' => false,   // Log all requests
    'max_log_entries' => 10000,       // Keep more entries
],
```

## 📈 Scaling Considerations

### High Traffic

- **Redis integration**: For high-traffic sites
- **Load balancing**: Distribute protection across servers
- **CDN integration**: Use Cloudflare or similar services

### Enterprise Features

- **API integration**: REST API for management
- **Webhook alerts**: Real-time notifications
- **Analytics**: Advanced reporting and metrics
- **Multi-tenant**: Support for multiple applications

## 🔐 Best Practices

### 1. Regular Monitoring

- Check dashboard daily
- Review blocked IPs weekly
- Analyze logs monthly
- Update whitelist as needed

### 2. Configuration Management

- Start with default settings
- Adjust based on traffic patterns
- Monitor false positive rates
- Document custom configurations

### 3. Security Updates

- Keep system updated
- Monitor security advisories
- Test protection regularly
- Backup configurations

### 4. Incident Response

- Document all incidents
- Analyze attack patterns
- Update protection rules
- Communicate with stakeholders

## 📞 Support

### Documentation

- This README file
- Code comments in source files
- Configuration examples
- Troubleshooting guide

### Community

- GitHub issues
- Security forums
- PHP community resources
- Web security groups

## 📄 License

This DDoS protection system is part of your church application and follows the same licensing terms.

---

**Note**: This system provides robust protection against common DDoS attacks but should be part of a comprehensive security strategy. Consider additional measures like:

- Web Application Firewall (WAF)
- CDN services (Cloudflare, AWS CloudFront)
- Server-level security
- Regular security audits
- Professional security consulting
