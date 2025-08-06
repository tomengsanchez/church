<?php
require_once 'app/config/config.php';
require_once 'app/Core/Database.php';
require_once 'app/Models/UserModel.php';

try {
    echo "Testing getPastorsWithChurches method...\n";
    
    $userModel = new \App\Models\UserModel();
    $pastors = $userModel->getPastorsWithChurches();
    
    echo "Found " . count($pastors) . " pastors:\n";
    
    foreach ($pastors as $pastor) {
        echo "- {$pastor['name']} (ID: {$pastor['id']}) -> ";
        echo "Church: " . ($pastor['church_name'] ?? 'None') . " (ID: " . ($pastor['church_id'] ?? 'None') . ")\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?> 