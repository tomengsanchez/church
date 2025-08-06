<?php
require_once 'app/config/config.php';
require_once 'app/Core/Database.php';

try {
    echo "Testing database connection...\n";
    $db = \App\Core\Database::getInstance();
    echo "✓ Database connected\n";
    
    echo "\nTesting direct query...\n";
    $sql = "SELECT u.id, u.name, u.church_id, c.name as church_name 
            FROM users u 
            LEFT JOIN churches c ON u.church_id = c.id 
            WHERE u.role = 'pastor' 
            ORDER BY u.name ASC";
    
    $pastors = $db->fetchAll($sql);
    echo "Found " . count($pastors) . " pastors:\n";
    
    foreach ($pastors as $pastor) {
        echo "- {$pastor['name']} -> " . ($pastor['church_name'] ?? 'No Church') . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 