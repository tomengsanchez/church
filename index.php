<?php
session_start();

// Load Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Load configuration
require_once __DIR__ . '/app/config/config.php';

use App\Core\Router;
use App\Core\Database;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\ChurchController;
use App\Controllers\PastorController;
use App\Controllers\CoachController;
use App\Controllers\MentorController;
use App\Controllers\MemberController;
use App\Controllers\ErrorLogController;
use App\Controllers\ChurchEventController;
use App\Controllers\SatelifeEventController;
use App\Controllers\LifegroupEventController;
use App\Controllers\LifegroupController;
use App\Controllers\SettingsController;
use App\Controllers\FriendController;

// Initialize database connection
Database::getInstance();

// Initialize router
$router = new Router();

// Define routes
$router->get('/', [DashboardController::class, 'index']);
$router->get('/home', [DashboardController::class, 'index']);

// Auth routes
$router->get('/auth/login', [AuthController::class, 'login']);
$router->post('/auth/login', [AuthController::class, 'login']);
$router->get('/auth/logout', [AuthController::class, 'logout']);
$router->get('/auth/register', [AuthController::class, 'register']);
$router->post('/auth/register', [AuthController::class, 'register']);
$router->get('/auth/profile', [AuthController::class, 'profile']);
$router->post('/auth/profile', [AuthController::class, 'profile']);

// Dashboard routes
$router->get('/dashboard', [DashboardController::class, 'index']);

// Church routes
$router->get('/church', [ChurchController::class, 'index']);
$router->get('/church/create', [ChurchController::class, 'create']);
$router->post('/church/create', [ChurchController::class, 'store']);
$router->get('/church/edit/{id}', [ChurchController::class, 'edit']);
$router->post('/church/edit/{id}', [ChurchController::class, 'update']);
$router->post('/church/delete/{id}', [ChurchController::class, 'delete']);
$router->get('/church/fix-pastors', [ChurchController::class, 'fixPastorAssignments']);

// Pastor routes
$router->get('/pastor', [PastorController::class, 'index']);
$router->get('/pastor/create', [PastorController::class, 'create']);
$router->post('/pastor/create', [PastorController::class, 'store']);
$router->get('/pastor/edit/{id}', [PastorController::class, 'edit']);
$router->post('/pastor/edit/{id}', [PastorController::class, 'update']);
$router->post('/pastor/delete/{id}', [PastorController::class, 'delete']);

// Coach routes
$router->get('/coach', [CoachController::class, 'index']);
$router->get('/coach/create', [CoachController::class, 'create']);
$router->post('/coach/create', [CoachController::class, 'store']);
$router->get('/coach/edit/{id}', [CoachController::class, 'edit']);
$router->post('/coach/edit/{id}', [CoachController::class, 'update']);
$router->post('/coach/delete/{id}', [CoachController::class, 'delete']);

// Mentor routes
$router->get('/mentor', [MentorController::class, 'index']);
$router->get('/mentor/create', [MentorController::class, 'create']);
$router->post('/mentor/create', [MentorController::class, 'store']);
$router->get('/mentor/edit/{id}', [MentorController::class, 'edit']);
$router->post('/mentor/edit/{id}', [MentorController::class, 'update']);
$router->post('/mentor/delete/{id}', [MentorController::class, 'delete']);
$router->get('/mentor/coaches/{churchId}', [MentorController::class, 'getCoachesByChurch']);

// Lifegroup routes
$router->get('/lifegroup', [LifegroupController::class, 'index']);
$router->get('/lifegroup/create', [LifegroupController::class, 'create']);
$router->post('/lifegroup/create', [LifegroupController::class, 'store']);
$router->get('/lifegroup/edit/{id}', [LifegroupController::class, 'edit']);
$router->post('/lifegroup/edit/{id}', [LifegroupController::class, 'update']);
$router->post('/lifegroup/update/{id}', [LifegroupController::class, 'update']);
$router->post('/lifegroup/delete/{id}', [LifegroupController::class, 'delete']);
$router->get('/lifegroup/view/{id}', [LifegroupController::class, 'show']);
$router->get('/lifegroup/mentors/{churchId}', [LifegroupController::class, 'getMentorsByChurch']);

// Member routes
$router->get('/member', [MemberController::class, 'index']);
$router->get('/member/create', [MemberController::class, 'create']);
$router->post('/member/create', [MemberController::class, 'store']);
// Friend routes
$router->get('/friend', [FriendController::class, 'index']);
$router->get('/friend/create', [FriendController::class, 'create']);
$router->post('/friend', [FriendController::class, 'store']);
$router->get('/friend/edit/{id}', [FriendController::class, 'edit']);
$router->post('/friend/edit/{id}', [FriendController::class, 'update']);
$router->post('/friend/delete/{id}', [FriendController::class, 'delete']);
$router->get('/friend/promote/{id}', [FriendController::class, 'promote']);
$router->post('/friend/promote/{id}', [FriendController::class, 'processPromotion']);
$router->get('/member/edit/{id}', [MemberController::class, 'edit']);
$router->post('/member/edit/{id}', [MemberController::class, 'update']);
$router->post('/member/delete/{id}', [MemberController::class, 'delete']);
$router->post('/member/status/{id}', [MemberController::class, 'updateStatus']);
$router->get('/member/coaches/{churchId}', [MemberController::class, 'getCoachesByChurch']);
$router->get('/member/mentors/{churchId}', [MemberController::class, 'getMentorsByChurch']);
$router->get('/member/mentors-by-coach/{coachId}', [MemberController::class, 'getMentorsByCoach']);
$router->get('/member/lifegroups-by-mentor/{mentorId}', [MemberController::class, 'getLifegroupsByMentor']);

// Event routes
$router->get('/events/church', [ChurchEventController::class, 'index']);
$router->get('/events/satelife', [SatelifeEventController::class, 'index']);
$router->get('/events/lifegroup', [LifegroupEventController::class, 'index']);
// Lifegroup event CRUD
$router->get('/events/lifegroup/create', [LifegroupEventController::class, 'create']);
$router->post('/events/lifegroup/create', [LifegroupEventController::class, 'store']);
$router->get('/events/lifegroup/edit/{id}', [LifegroupEventController::class, 'edit']);
$router->post('/events/lifegroup/edit/{id}', [LifegroupEventController::class, 'update']);
$router->post('/events/lifegroup/delete/{id}', [LifegroupEventController::class, 'delete']);
$router->get('/events/lifegroup/view/{id}', [LifegroupEventController::class, 'show']);
$router->post('/events/lifegroup/duplicate/{id}', [LifegroupEventController::class, 'duplicate']);
// Lifegroup event attendance helper
$router->get('/events/lifegroup/members/{lifegroupId}', [LifegroupEventController::class, 'getMembers']);

// Error Log routes
$router->get('/errorlog', [ErrorLogController::class, 'index']);
$router->get('/errorlog/view/{id}', [ErrorLogController::class, 'view']);
$router->get('/errorlog/stats', [ErrorLogController::class, 'stats']);
$router->post('/errorlog/delete/{id}', [ErrorLogController::class, 'delete']);
$router->post('/errorlog/clear-old', [ErrorLogController::class, 'clearOld']);
$router->post('/errorlog/clear-all', [ErrorLogController::class, 'clearAll']);
$router->get('/errorlog/export', [ErrorLogController::class, 'export']);

// Settings routes
$router->get('/settings', [SettingsController::class, 'index']);
$router->post('/settings/status/create', [SettingsController::class, 'createStatus']);
$router->post('/settings/status/edit/{id}', [SettingsController::class, 'editStatus']);
$router->post('/settings/status/delete/{id}', [SettingsController::class, 'deleteStatus']);
$router->post('/settings/status/sort', [SettingsController::class, 'updateSortOrder']);

// Test route for demonstrating error logging
$router->get('/test-logs', function() {
    $logger = new \App\Core\Logger();
    
    // Log different types of messages
    $logger->info('This is an informational message', ['test' => true]);
    $logger->warning('This is a warning message', ['test' => true]);
    $logger->error('This is an error message', ['test' => true]);
    $logger->debug('This is a debug message', ['test' => true]);
    
    echo '<h1>Test Logs Created</h1>';
    echo '<p>Check the error logs section to see the test entries.</p>';
    echo '<p><a href="/errorlog">View Error Logs</a></p>';
    echo '<p><a href="/">Go Home</a></p>';
});

// Dispatch the request
$router->dispatch();
?> 