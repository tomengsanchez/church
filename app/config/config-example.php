<?php
// Application Configuration
define('APP_NAME', 'LIFEGIVER CHURCH');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://churchapp.local');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'churchapp');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Session Configuration
define('SESSION_TIMEOUT', 3600); // 1 hour

// File Upload Configuration
define('UPLOAD_PATH', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Pagination
define('ITEMS_PER_PAGE', 10);

// User Roles
define('ROLE_SUPER_ADMIN', 'super_admin');
define('ROLE_PASTOR', 'pastor');
define('ROLE_COACH', 'coach');
define('ROLE_MENTOR', 'mentor');
define('ROLE_MEMBER', 'member');

// Member Status
define('STATUS_ACTIVE', 'active');
define('STATUS_INACTIVE', 'inactive');
define('STATUS_PENDING', 'pending');
define('STATUS_SUSPENDED', 'suspended');

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Custom Error Handler
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $logger = new \App\Core\Logger();
    
    $level = match($errno) {
        E_ERROR => 'error',
        E_WARNING => 'warning',
        E_PARSE => 'critical',
        E_NOTICE => 'notice',
        E_CORE_ERROR => 'critical',
        E_CORE_WARNING => 'warning',
        E_COMPILE_ERROR => 'critical',
        E_COMPILE_WARNING => 'warning',
        E_USER_ERROR => 'error',
        E_USER_WARNING => 'warning',
        E_USER_NOTICE => 'notice',
        E_STRICT => 'info',
        E_RECOVERABLE_ERROR => 'error',
        E_DEPRECATED => 'warning',
        E_USER_DEPRECATED => 'warning',
        default => 'error'
    };
    
    $context = [
        'file' => $errfile,
        'line' => $errline,
        'errno' => $errno
    ];
    
    $logger->$level($errstr, $context);
    
    // Don't execute PHP internal error handler
    return true;
}

// Set custom error handler
set_error_handler('customErrorHandler');

// Exception Handler
function customExceptionHandler($exception) {
    $logger = new \App\Core\Logger();
    
    $context = [
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ];
    
    $logger->error($exception->getMessage(), $context);
    
    // Show user-friendly error page
    http_response_code(500);
    if (file_exists("app/views/errors/500.php")) {
        include "app/views/errors/500.php";
    } else {
        echo "An error occurred. Please try again later.";
    }
    exit;
}

// Set custom exception handler
set_exception_handler('customExceptionHandler');

// Fatal Error Handler
function customFatalErrorHandler() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $logger = new \App\Core\Logger();
        
        $context = [
            'file' => $error['file'],
            'line' => $error['line']
        ];
        
        $logger->critical($error['message'], $context);
        
        // Show user-friendly error page
        http_response_code(500);
        if (file_exists("app/views/errors/500.php")) {
            include "app/views/errors/500.php";
        } else {
            echo "A critical error occurred. Please try again later.";
        }
        exit;
    }
}

// Register shutdown function for fatal errors
register_shutdown_function('customFatalErrorHandler');

// Helper Functions
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function flash(string $message, string $type = 'info'): void
{
    setFlash($type, $message);
}

function hasPermission(string $role): bool
{
    if (!isset($_SESSION['user_role'])) {
        return false;
    }
    
    $userRole = $_SESSION['user_role'];
    $roleHierarchy = [
        ROLE_SUPER_ADMIN => 5,
        ROLE_PASTOR => 4,
        ROLE_COACH => 3,
        ROLE_MENTOR => 2,
        ROLE_MEMBER => 1
    ];
    
    return isset($roleHierarchy[$userRole]) && 
           isset($roleHierarchy[$role]) && 
           $roleHierarchy[$userRole] >= $roleHierarchy[$role];
}

function isAuthenticated(): bool
{
    return isset($_SESSION['user_id']);
}

function redirect(string $url): void
{
    header("Location: $url");
    exit;
}

function asset(string $path): string
{
    return BASE_URL . '/assets/' . ltrim($path, '/');
}

function formatDate(string $date): string
{
    return date('M j, Y', strtotime($date));
}

function formatDateTime(string $date): string
{
    return date('M j, Y g:i A', strtotime($date));
}

function getStatusBadgeClass(string $status): string
{
    switch ($status) {
        case 'active': return 'success';
        case 'inactive': return 'secondary';
        case 'pending': return 'warning';
        case 'suspended': return 'danger';
        default: return 'secondary';
    }
}

function getTimeAgo(string $timestamp): string
{
    $time = time() - strtotime($timestamp);
    
    if ($time < 60) {
        return $time . ' seconds ago';
    } elseif ($time < 3600) {
        $minutes = floor($time / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($time < 86400) {
        $hours = floor($time / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } else {
        $days = floor($time / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    }
}

function sanitizeInput(string $input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validateEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function generateRandomString(int $length = 10): string
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $string = '';
    
    for ($i = 0; $i < $length; $i++) {
        $string .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    return $string;
}

function logError(string $message, array $context = []): void
{
    $logger = new \App\Core\Logger();
    $logger->error($message, $context);
}

function logInfo(string $message, array $context = []): void
{
    $logger = new \App\Core\Logger();
    $logger->info($message, $context);
}

function logWarning(string $message, array $context = []): void
{
    $logger = new \App\Core\Logger();
    $logger->warning($message, $context);
}

function logDebug(string $message, array $context = []): void
{
    $logger = new \App\Core\Logger();
    $logger->debug($message, $context);
} 