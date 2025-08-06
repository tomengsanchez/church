<?php
require_once 'app/config/config.php';
require_once 'app/Core/Database.php';
require_once 'app/Models/UserModel.php';

try {
    $db = \App\Core\Database::getInstance();
    $userModel = new \App\Models\UserModel();
    
    echo "Current pastor assignments:\n";
    echo "========================\n";
    
    $pastors = $db->fetchAll("SELECT u.id, u.name, u.church_id, c.name as church_name 
                              FROM users u 
                              LEFT JOIN churches c ON u.church_id = c.id 
                              WHERE u.role = 'pastor'");
    
    foreach ($pastors as $pastor) {
        echo "Pastor: {$pastor['name']} (ID: {$pastor['id']}) -> ";
        echo "Church: " . ($pastor['church_name'] ?? 'None') . " (ID: " . ($pastor['church_id'] ?? 'None') . ")\n";
    }
    
    echo "\nUsing getPastorsWithChurches method:\n";
    echo "====================================\n";
    
    $pastorsWithChurches = $userModel->getPastorsWithChurches();
    foreach ($pastorsWithChurches as $pastor) {
        echo "Pastor: {$pastor['name']} (ID: {$pastor['id']}) -> ";
        echo "Church: " . ($pastor['church_name'] ?? 'None') . " (ID: " . ($pastor['church_id'] ?? 'None') . ")\n";
    }
    
    echo "\nCurrent church pastor assignments:\n";
    echo "==================================\n";
    
    $churches = $db->fetchAll("SELECT c.id, c.name, c.pastor_id, u.name as pastor_name 
                               FROM churches c 
                               LEFT JOIN users u ON c.pastor_id = u.id");
    
    foreach ($churches as $church) {
        echo "Church: {$church['name']} (ID: {$church['id']}) -> ";
        echo "Pastor: " . ($church['pastor_name'] ?? 'None') . " (ID: " . ($church['pastor_id'] ?? 'None') . ")\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 