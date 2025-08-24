<?php
/**
 * Database Seeder for Satelife Events with Attendees
 * This script creates 1000 test records for Satelife Events with past dates
 * and generates attendance records to test the pagination system
 */

require_once 'app/config/config.php';
require_once 'app/Core/Database.php';
require_once 'app/Core/Logger.php';

use App\Core\Database;

// Initialize database
$db = Database::getInstance();

echo "🚀 Starting Satelife Events Seeder with Attendees...\n\n";

// Check if coach_id column exists
$columnExists = $db->fetch("
    SELECT COUNT(*) as count 
    FROM information_schema.columns 
    WHERE table_schema = DATABASE() 
    AND table_name = 'satelife_events' 
    AND column_name = 'coach_id'
");

if ($columnExists['count'] == 0) {
    echo "❌ Error: coach_id column does not exist in satelife_events table.\n";
    echo "Please run the migration: database/0011_add_coach_id_to_satelife_events.sql\n";
    exit(1);
}

echo "✅ coach_id column found in satelife_events table\n\n";

// Sample data arrays
$eventTitles = [
    'Bible Study', 'Prayer Meeting', 'Youth Fellowship', 'Worship Service', 
    'Community Outreach', 'Leadership Training', 'Family Day', 'Evangelism', 
    'Discipleship Class', 'Praise and Worship', 'Testimony Night', 'Mission Trip', 
    'Retreat', 'Conference', 'Seminar', 'Workshop', 'Cell Group', 
    'Men\'s Ministry', 'Women\'s Ministry', 'Children\'s Ministry'
];

$locations = [
    'Main Hall', 'Fellowship Hall', 'Prayer Room', 'Youth Center', 
    'Conference Room', 'Outdoor Area', 'Community Center', 'School Auditorium', 
    'Park', 'Beach', 'Mountain Retreat', 'City Hall', 'Library', 
    'Hospital', 'Nursing Home', 'Shopping Mall', 'University Campus', 
    'Sports Complex', 'Theater', 'Restaurant'
];

$descriptions = [
    'Weekly gathering for spiritual growth', 'Monthly prayer and intercession', 
    'Youth activities and fellowship', 'Sunday worship service', 
    'Community service and outreach', 'Leadership development program', 
    'Family bonding activities', 'Evangelism and outreach', 
    'Discipleship and mentoring', 'Praise and worship session', 
    'Testimony and sharing night', 'Mission and outreach trip', 
    'Spiritual retreat and renewal', 'Christian conference', 
    'Educational seminar', 'Skills development workshop', 
    'Small group fellowship', 'Men\'s ministry activities', 
    'Women\'s ministry activities', 'Children\'s ministry program'
];

$eventTimes = [
    '01:00:00', '02:00:00', '03:00:00', '04:00:00', '05:00:00', '06:00:00',
    '07:00:00', '08:00:00', '09:00:00', '10:00:00', '11:00:00', '12:00:00',
    '13:00:00', '14:00:00', '15:00:00', '16:00:00', '17:00:00', '18:00:00',
    '19:00:00', '20:00:00', '21:00:00', '22:00:00', '23:00:00'
];

// Get existing data for references
$churches = $db->fetchAll("SELECT id FROM churches");
$coaches = $db->fetchAll("SELECT id, name, satelife_name FROM users WHERE role = 'coach'");
$members = $db->fetchAll("SELECT id FROM users WHERE role = 'member'");
$admin = $db->fetch("SELECT id FROM users WHERE role = 'super_admin' LIMIT 1");

if (empty($churches)) {
    echo "❌ No churches found in database. Please add churches first.\n";
    exit(1);
}

if (empty($coaches)) {
    echo "⚠️  No coaches found. Will use admin as creator.\n";
    $coaches = [$admin];
}

if (empty($members)) {
    echo "⚠️  No members found. Will create events without attendees.\n";
}

if (!$admin) {
    echo "❌ No admin user found. Please ensure there's a super_admin user.\n";
    exit(1);
}

$adminId = $admin['id'];
$churchIds = array_column($churches, 'id');
$coachIds = array_column($coaches, 'id');
$memberIds = array_column($members, 'id');

echo "📊 Found " . count($churches) . " churches, " . count($coaches) . " coaches, and " . count($members) . " members\n";

// Show coach details
if (!empty($coaches)) {
    echo "👥 Available Coaches:\n";
    foreach ($coaches as $coach) {
        $satelifeInfo = $coach['satelife_name'] ? " ({$coach['satelife_name']})" : "";
        echo "   - {$coach['name']}{$satelifeInfo}\n";
    }
    echo "\n";
}
echo "👤 Using admin ID: {$adminId}\n\n";

// Check if we want to clear existing test data
echo "Do you want to clear existing Satelife Events before seeding? (y/N): ";
$handle = fopen("php://stdin", "r");
$clearExisting = trim(fgets($handle));
fclose($handle);

if (strtolower($clearExisting) === 'y') {
    echo "🗑️  Clearing existing Satelife Events and attendees...\n";
    $db->query("DELETE FROM event_attendees WHERE event_type = 'satelife' AND event_id IN (SELECT id FROM satelife_events WHERE title LIKE '% #%')");
    $db->query("DELETE FROM satelife_events WHERE title LIKE '% #%'");
    echo "✅ Cleared existing test data\n\n";
}

// Start seeding
echo "🌱 Starting to seed 1000 Satelife Events with attendees...\n";
$startTime = microtime(true);

$insertedCount = 0;
$attendeeCount = 0;
$batchSize = 100;
$batch = [];

for ($i = 1; $i <= 1000; $i++) {
    // Generate random past date (within last 2 years)
    $daysAgo = rand(1, 730);
    $eventDate = date('Y-m-d', strtotime("-{$daysAgo} days"));
    
    // Get random values
    $eventTitle = $eventTitles[array_rand($eventTitles)];
    $eventLocation = $locations[array_rand($locations)];
    $eventDescription = $descriptions[array_rand($descriptions)];
    $eventTime = $eventTimes[array_rand($eventTimes)];
    
    // Get random church and coach
    $churchId = $churchIds[array_rand($churchIds)];
    $coachId = $coachIds[array_rand($coachIds)];
    
    // Determine status based on date
    if (strtotime($eventDate) < strtotime('today')) {
        $eventStatus = 'completed';
    } elseif (strtotime($eventDate) == strtotime('today')) {
        $eventStatus = 'ongoing';
    } else {
        $eventStatus = 'upcoming';
    }
    
    // Generate random created_at date
    $createdDaysAgo = rand(1, 730);
    $createdAt = date('Y-m-d H:i:s', strtotime("-{$createdDaysAgo} days"));
    
    // Prepare batch insert
    $batch[] = [
        'title' => "{$eventTitle} #{$i}",
        'description' => "{$eventDescription} - Event number {$i}",
        'event_date' => $eventDate,
        'event_time' => $eventTime,
        'location' => $eventLocation,
        'church_id' => $churchId,
        'coach_id' => $coachId,
        'status' => $eventStatus,
        'created_by' => $adminId,
        'created_at' => $createdAt
    ];
    
    // Insert batch when it reaches batch size
    if (count($batch) >= $batchSize) {
        $eventIds = insertBatch($db, $batch);
        $insertedCount += count($batch);
        
        // Add attendees for these events
        if (!empty($memberIds)) {
            $attendeeCount += addAttendeesForEvents($db, $eventIds, $memberIds, $adminId);
        }
        
        echo "✅ Inserted {$insertedCount}/1000 events with attendees\n";
        $batch = [];
    }
}

// Insert remaining batch
if (!empty($batch)) {
    $eventIds = insertBatch($db, $batch);
    $insertedCount += count($batch);
    
    // Add attendees for remaining events
    if (!empty($memberIds)) {
        $attendeeCount += addAttendeesForEvents($db, $eventIds, $memberIds, $adminId);
    }
}

$endTime = microtime(true);
$executionTime = round($endTime - $startTime, 2);

echo "\n🎉 Seeding completed!\n";
echo "📈 Total events inserted: {$insertedCount}\n";
echo "👥 Total attendees added: {$attendeeCount}\n";
echo "⏱️  Execution time: {$executionTime} seconds\n\n";

// Show statistics
echo "📊 Database Statistics:\n";
$stats = $db->fetch("
    SELECT 
        COUNT(*) as total_events,
        MIN(event_date) as earliest_date,
        MAX(event_date) as latest_date,
        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_events,
        COUNT(CASE WHEN status = 'ongoing' THEN 1 END) as ongoing_events,
        COUNT(CASE WHEN status = 'upcoming' THEN 1 END) as upcoming_events,
        COUNT(DISTINCT coach_id) as unique_coaches
    FROM satelife_events
");

$attendeeStats = $db->fetch("
    SELECT 
        COUNT(*) as total_attendees,
        COUNT(DISTINCT event_id) as events_with_attendees,
        COUNT(DISTINCT user_id) as unique_attendees
    FROM event_attendees 
    WHERE event_type = 'satelife'
");

echo "   Total Events: {$stats['total_events']}\n";
echo "   Date Range: {$stats['earliest_date']} to {$stats['latest_date']}\n";
echo "   Completed: {$stats['completed_events']}\n";
echo "   Ongoing: {$stats['ongoing_events']}\n";
echo "   Upcoming: {$stats['upcoming_events']}\n";
echo "   Unique Coaches: {$stats['unique_coaches']}\n";
echo "   Total Attendees: {$attendeeStats['total_attendees']}\n";
echo "   Events with Attendees: {$attendeeStats['events_with_attendees']}\n";
echo "   Unique Attendees: {$attendeeStats['unique_attendees']}\n\n";

// Show sample of created events with attendee count
echo "📋 Sample of created events:\n";
$sampleEvents = $db->fetchAll("
    SELECT 
        e.id,
        e.title,
        e.event_date,
        e.event_time,
        e.location,
        e.status,
        e.created_at,
        u.name as coach_name,
        u.satelife_name,
        COUNT(ea.id) as attendee_count
    FROM satelife_events e
    LEFT JOIN users u ON e.coach_id = u.id
    LEFT JOIN event_attendees ea ON e.id = ea.event_id AND ea.event_type = 'satelife'
    WHERE e.title LIKE '% #%'
    GROUP BY e.id
    ORDER BY e.created_at DESC 
    LIMIT 5
");

foreach ($sampleEvents as $event) {
    $coachInfo = $event['coach_name'] ? "Coach: {$event['coach_name']}" . ($event['satelife_name'] ? " ({$event['satelife_name']})" : "") : "No Coach";
    echo "   ID: {$event['id']} | {$event['title']} | {$event['event_date']} {$event['event_time']} | {$event['location']} | {$event['status']} | {$coachInfo} | {$event['attendee_count']} attendees\n";
}

echo "\n✨ Seeder completed successfully! You can now test pagination at /events/satelife\n";

/**
 * Insert a batch of events and return their IDs
 */
function insertBatch($db, $batch) {
    if (empty($batch)) return [];
    
    $sql = "INSERT INTO satelife_events (title, description, event_date, event_time, location, church_id, coach_id, status, created_by, created_at) VALUES ";
    $values = [];
    $params = [];
    
    foreach ($batch as $event) {
        $values[] = "(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $params[] = $event['title'];
        $params[] = $event['description'];
        $params[] = $event['event_date'];
        $params[] = $event['event_time'];
        $params[] = $event['location'];
        $params[] = $event['church_id'];
        $params[] = $event['coach_id'];
        $params[] = $event['status'];
        $params[] = $event['created_by'];
        $params[] = $event['created_at'];
    }
    
    $sql .= implode(', ', $values);
    $db->query($sql, $params);
    
    // Get the IDs of the inserted events
    $lastInsertId = $db->lastInsertId();
    $eventIds = [];
    for ($i = 0; $i < count($batch); $i++) {
        $eventIds[] = $lastInsertId + $i;
    }
    
    return $eventIds;
}

/**
 * Add attendees for a batch of events
 */
function addAttendeesForEvents($db, $eventIds, $memberIds, $adminId) {
    if (empty($eventIds) || empty($memberIds)) return 0;
    
    $attendeeBatch = [];
    $attendeeCount = 0;
    
    foreach ($eventIds as $eventId) {
        // Randomly decide how many attendees (1-10 members per event)
        $numAttendees = rand(1, min(10, count($memberIds)));
        $selectedMembers = array_rand($memberIds, $numAttendees);
        
        // Ensure selectedMembers is always an array
        if (!is_array($selectedMembers)) {
            $selectedMembers = [$selectedMembers];
        }
        
        foreach ($selectedMembers as $memberIndex) {
            $memberId = $memberIds[$memberIndex];
            
            // Randomly decide if member attended (70% attendance rate for past events)
            $attended = rand(1, 100) <= 70 ? 'attended' : 'absent';
            
            $attendeeBatch[] = [
                'event_type' => 'satelife',
                'event_id' => $eventId,
                'user_id' => $memberId,
                'status' => $attended,
                'created_by' => $adminId,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $attendeeCount++;
        }
    }
    
    // Insert attendees in batches
    if (!empty($attendeeBatch)) {
        insertAttendeeBatch($db, $attendeeBatch);
    }
    
    return $attendeeCount;
}

/**
 * Insert a batch of attendees
 */
function insertAttendeeBatch($db, $batch) {
    if (empty($batch)) return;
    
    $sql = "INSERT INTO event_attendees (event_type, event_id, user_id, status, created_by, created_at) VALUES ";
    $values = [];
    $params = [];
    
    foreach ($batch as $attendee) {
        $values[] = "(?, ?, ?, ?, ?, ?)";
        $params[] = $attendee['event_type'];
        $params[] = $attendee['event_id'];
        $params[] = $attendee['user_id'];
        $params[] = $attendee['status'];
        $params[] = $attendee['created_by'];
        $params[] = $attendee['created_at'];
    }
    
    $sql .= implode(', ', $values);
    $db->query($sql, $params);
}
