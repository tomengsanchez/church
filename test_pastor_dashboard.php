<?php
// Test file for Pastor Dashboard functionality
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/config/config.php';

use App\Models\MemberModel;
use App\Core\Database;

// Initialize database
Database::getInstance();

$memberModel = new MemberModel();

// Test church ID (you may need to adjust this based on your data)
$churchId = 1;

echo "<h1>Pastor Dashboard Test</h1>";

// Test 1: Member Status Percentages
echo "<h2>1. Member Status Percentages</h2>";
try {
    $statusPercentages = $memberModel->getMemberStatusPercentages($churchId);
    echo "<pre>";
    print_r($statusPercentages);
    echo "</pre>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Test 1.5: Member Stats (Updated Logic)
echo "<h2>1.5. Member Stats (Updated Logic)</h2>";
try {
    $memberStats = $memberModel->getMemberStats($churchId);
    echo "<h3>New Logic:</h3>";
    echo "<ul>";
    echo "<li><strong>Total Members:</strong> " . ($memberStats['total_members'] ?? 0) . "</li>";
    echo "<li><strong>Active Members:</strong> " . ($memberStats['active_members'] ?? 0) . " (NOT 'pending' or 'new_friend')</li>";
    echo "<li><strong>Inactive Members:</strong> " . ($memberStats['inactive_members'] ?? 0) . " (status = 'inactive')</li>";
    echo "<li><strong>In Progress Members:</strong> " . ($memberStats['in_progress_members'] ?? 0) . " ('pending' or 'new_friend')</li>";
    echo "</ul>";
    echo "<pre>";
    print_r($memberStats);
    echo "</pre>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Test 2: Hierarchy Stats
echo "<h2>2. Hierarchy Stats</h2>";
try {
    $hierarchyStats = $memberModel->getHierarchyStats($churchId);
    echo "<pre>";
    print_r($hierarchyStats);
    echo "</pre>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Test 3: Coach Hierarchy Details (if coaches exist)
echo "<h2>3. Coach Hierarchy Details</h2>";
try {
    // Get first coach ID
    $coaches = $memberModel->findAll(['role' => 'coach', 'church_id' => $churchId]);
    if (!empty($coaches)) {
        $firstCoachId = $coaches[0]['id'];
        $coachHierarchy = $memberModel->getCoachHierarchyDetails($firstCoachId);
        echo "<pre>";
        print_r($coachHierarchy);
        echo "</pre>";
    } else {
        echo "No coaches found for church ID: $churchId";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Test 4: Mentor Hierarchy Details (if mentors exist)
echo "<h2>4. Mentor Hierarchy Details</h2>";
try {
    // Get first mentor ID
    $mentors = $memberModel->findAll(['role' => 'mentor', 'church_id' => $churchId]);
    if (!empty($mentors)) {
        $firstMentorId = $mentors[0]['id'];
        $mentorHierarchy = $memberModel->getMentorHierarchyDetails($firstMentorId);
        echo "<pre>";
        print_r($mentorHierarchy);
        echo "</pre>";
    } else {
        echo "No mentors found for church ID: $churchId";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Test 5: Lifegroups with Hierarchy Details
echo "<h2>5. Lifegroups with Hierarchy Details</h2>";
try {
    $lifegroupsHierarchy = $memberModel->getLifegroupsWithHierarchy($churchId);
    echo "<pre>";
    print_r($lifegroupsHierarchy);
    echo "</pre>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

echo "<hr>";
echo "<p><a href='/'>Back to Dashboard</a></p>";
?>
