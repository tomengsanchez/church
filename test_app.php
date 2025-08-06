<?php
require_once 'app/config/config.php';
require_once 'app/Core/Database.php';
require_once 'app/Models/UserModel.php';

try {
    echo "Testing application...\n";
    
    // Test database connection
    $db = \App\Core\Database::getInstance();
    echo "✓ Database connected\n";
    
    // Test UserModel
    $userModel = new \App\Models\UserModel();
    echo "✓ UserModel created\n";
    
    // Test getPastorsWithChurches method
    echo "Testing getPastorsWithChurches...\n";
    $pastors = $userModel->getPastorsWithChurches();
    echo "✓ Found " . count($pastors) . " pastors\n";
    
    // Display results
    foreach ($pastors as $pastor) {
        echo "- {$pastor['name']} -> " . ($pastor['church_name'] ?? 'No Church') . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?> 