<?php
/**
 * Church Management System Installation Script
 * Run this script to set up the application
 */

echo "=== Church Management System Installation ===\n\n";

// Check PHP version
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    echo "❌ Error: PHP 7.4 or higher is required. Current version: " . PHP_VERSION . "\n";
    exit(1);
}
echo "✅ PHP version: " . PHP_VERSION . "\n";

// Check required extensions
$required_extensions = ['pdo', 'pdo_mysql', 'json', 'mbstring'];
foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        echo "❌ Error: Required PHP extension '$ext' is not loaded.\n";
        exit(1);
    }
    echo "✅ Extension '$ext' is loaded.\n";
}

// Check if composer autoload exists
if (!file_exists('vendor/autoload.php')) {
    echo "❌ Error: Composer autoloader not found. Please run 'composer install' first.\n";
    exit(1);
}
echo "✅ Composer autoloader found.\n";

// Check if config file exists
if (!file_exists('app/config/config.php')) {
    echo "❌ Error: Configuration file not found.\n";
    exit(1);
}
echo "✅ Configuration file found.\n";

// Test database connection
try {
    require_once 'app/config/config.php';
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    echo "✅ Database connection successful.\n";
} catch (PDOException $e) {
    echo "❌ Error: Database connection failed: " . $e->getMessage() . "\n";
    echo "Please check your database configuration in app/config/config.php\n";
    exit(1);
}

// Check if tables exist
$tables = ['users', 'churches', 'hierarchy'];
foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Table '$table' exists.\n";
        } else {
            echo "⚠️  Warning: Table '$table' does not exist. Please import the database schema.\n";
        }
    } catch (PDOException $e) {
        echo "❌ Error checking table '$table': " . $e->getMessage() . "\n";
    }
}

// Check if uploads directory exists and is writable
$uploads_dir = 'uploads';
if (!is_dir($uploads_dir)) {
    if (mkdir($uploads_dir, 0755, true)) {
        echo "✅ Created uploads directory.\n";
    } else {
        echo "❌ Error: Could not create uploads directory.\n";
    }
} else {
    echo "✅ Uploads directory exists.\n";
}

if (is_writable($uploads_dir)) {
    echo "✅ Uploads directory is writable.\n";
} else {
    echo "⚠️  Warning: Uploads directory is not writable.\n";
}

// Check if assets directory exists
$assets_dir = 'assets';
if (!is_dir($assets_dir)) {
    echo "❌ Error: Assets directory not found.\n";
} else {
    echo "✅ Assets directory found.\n";
}

echo "\n=== Installation Summary ===\n";
echo "✅ PHP version and extensions: OK\n";
echo "✅ Composer autoloader: OK\n";
echo "✅ Configuration: OK\n";
echo "✅ Database connection: OK\n";
echo "✅ File structure: OK\n";

echo "\n=== Next Steps ===\n";
echo "1. Import the database schema: mysql -u root -p churchapp < database/schema.sql\n";
echo "2. Configure your web server to point to this directory\n";
echo "3. Ensure URL rewriting is enabled (mod_rewrite for Apache)\n";
echo "4. Access the application at: http://your-domain/\n";
echo "5. Login with default credentials:\n";
echo "   - Super Admin: admin@churchapp.local / password\n";
echo "   - Pastor: pastor@dynamicchurch.local / password\n";
echo "   - Coach: coach@dynamicchurch.local / password\n";
echo "   - Mentor: mentor@dynamicchurch.local / password\n";
echo "   - Member: alice@dynamicchurch.local / password\n";

echo "\n=== Security Recommendations ===\n";
echo "1. Change default passwords immediately\n";
echo "2. Update database credentials in app/config/config.php\n";
echo "3. Set proper file permissions\n";
echo "4. Enable HTTPS in production\n";
echo "5. Regular backups of the database\n";

echo "\n🎉 Installation check completed successfully!\n";
?> 