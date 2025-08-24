<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Pagination;
use App\Models\SatelifeEventModel;
use App\Models\ChurchModel;
use App\Models\MemberModel;
use App\Models\EventAttendeeModel;
use App\Models\CoachModel;

class SatelifeEventController extends Controller
{
    private SatelifeEventModel $eventModel;
    private ChurchModel $churchModel;
    private MemberModel $memberModel;
    private EventAttendeeModel $attendeeModel;
    private CoachModel $coachModel;
    
    public function __construct()
    {
        $this->eventModel = new SatelifeEventModel();
        $this->churchModel = new ChurchModel();
        $this->memberModel = new MemberModel();
        $this->attendeeModel = new EventAttendeeModel();
        $this->coachModel = new CoachModel();
    }
    
    public function index(): void
    {
        // Allow coaches and above to access satelife events
        $this->requireRole(ROLE_COACH);
        
        $userRole = $this->getUserRole();
        $userId = $_SESSION['user_id'];
        $churchId = $_SESSION['church_id'] ?? null;
        
        // Get pagination parameters
        $page = (int) ($_GET['page'] ?? 1);
        $perPage = (int) ($_GET['per_page'] ?? 10);
        $searchTerm = $_GET['search'] ?? '';
        $sortField = $_GET['sort'] ?? '';
        $sortDirection = $_GET['direction'] ?? 'asc';
        
        // Validate page size
        $allowedPageSizes = [5, 10, 50, 100, 200, 500];
        if (!in_array($perPage, $allowedPageSizes)) {
            $perPage = 10;
        }
        
        // Build additional filters based on user role and filters
        $additionalFilters = [];
        
        if ($userRole === ROLE_PASTOR) {
            // Pastor can see events in their church
            $additionalFilters['e.church_id'] = $churchId;
        } elseif ($userRole === ROLE_COACH) {
            // Coaches can only see their own events
            $additionalFilters['e.coach_id'] = $userId;
        }
        
        // Add coach filter if provided (only for pastors and admins)
        if ($userRole !== ROLE_COACH && !empty($_GET['coach_id'])) {
            $additionalFilters['coach_id'] = (int)$_GET['coach_id'];
        }
        
        // Get paginated data
        $result = $this->eventModel->getPaginatedEventsWithDetails(
            $page,
            $perPage,
            $searchTerm,
            $sortField,
            $sortDirection,
            $additionalFilters
        );
        
        // Create pagination object
        $pagination = new Pagination(
            $result['items'],
            $result['page'],
            $result['per_page'],
            $result['total'],
            $searchTerm,
            $sortField,
            $sortDirection,
            $additionalFilters,
            '/events/satelife'
        );
        
        $this->view('events/satelife/index', [
            'title' => 'Satelife Events',
            'events' => $result['items'],
            'pagination' => $pagination,
            'churches' => $this->churchModel->getAllChurches(),
            'coaches' => $this->getAllCoaches()
        ]);
    }

    public function create(): void
    {
        $this->requireRole(ROLE_COACH);
        $userRole = $this->getUserRole();
        $userId = $_SESSION['user_id'];
        $churchId = $_SESSION['church_id'] ?? null;
        
        // Pre-select based on user role
        $defaultChurchId = null;
        $defaultCoachId = null;
        
        if ($userRole === ROLE_PASTOR) {
            $defaultChurchId = $churchId;
        } elseif ($userRole === ROLE_COACH) {
            $defaultChurchId = $churchId;
            $defaultCoachId = $userId;
        }
        
        $this->view('events/satelife/create', [
            'title' => 'Add Satelife Event',
            'churches' => $this->churchModel->getAllChurches(),
            'coaches' => $this->getCoachesForChurch($defaultChurchId),
            'defaultChurchId' => $defaultChurchId,
            'defaultCoachId' => $defaultCoachId,
        ]);
    }

    public function store(): void
    {
        $this->requireRole(ROLE_COACH);

        $title = trim($_POST['title'] ?? '');
        $eventDate = trim($_POST['event_date'] ?? '');
        $eventTime = trim($_POST['event_time'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $churchId = (int)($_POST['church_id'] ?? 0);
        $coachId = (int)($_POST['coach_id'] ?? 0);

        if ($title === '' || $eventDate === '' || $churchId <= 0 || $coachId <= 0) {
            flash('Title, Date, Church and Coach are required.', 'danger');
            $this->redirect('/events/satelife/create');
        }

        $data = [
            'title' => $title,
            'description' => $description ?: null,
            'event_date' => $eventDate,
            'event_time' => $eventTime ?: null,
            'location' => $location ?: null,
            'church_id' => $churchId,
            'coach_id' => $coachId,
            'created_by' => (int)$_SESSION['user_id'],
            'status' => 'active',
        ];

        $eventId = $this->eventModel->create($data);

        // Attendance handling
        $attendees = isset($_POST['attendees']) && is_array($_POST['attendees']) ? array_map('intval', $_POST['attendees']) : [];
        $this->attendeeModel->setAttendedUsers('satelife', (int)$eventId, $attendees);

        flash('Satelife event created successfully!', 'success');
        $this->redirect('/events/satelife');
    }

    public function edit(int $id): void
    {
        $this->requireRole(ROLE_COACH);
        
        $event = $this->eventModel->getEventWithDetails($id);
        if (!$event) {
            flash('Event not found.', 'danger');
            $this->redirect('/events/satelife');
        }

        // Check if user can edit this event
        $userRole = $this->getUserRole();
        $userId = $_SESSION['user_id'];
        
        if ($userRole === ROLE_COACH) {
            // Coaches can only edit their own events
            if ($event['coach_id'] != $userId) {
                flash('You can only edit your own satelife events.', 'danger');
                $this->redirect('/events/satelife');
            }
        } elseif ($userRole !== ROLE_SUPER_ADMIN && $userRole !== ROLE_PASTOR && $event['created_by'] != $userId) {
            flash('You can only edit events you created.', 'danger');
            $this->redirect('/events/satelife');
        }

        $this->view('events/satelife/edit', [
            'title' => 'Edit Satelife Event',
            'event' => $event,
            'churches' => $this->churchModel->getAllChurches(),
            'coaches' => $this->getCoachesForChurch($event['church_id']),
            'attendees' => $this->attendeeModel->getAttendeeUsers('satelife', $id)
        ]);
    }

    public function update(int $id): void
    {
        $this->requireRole(ROLE_COACH);

        $event = $this->eventModel->findById($id);
        if (!$event) {
            flash('Event not found.', 'danger');
            $this->redirect('/events/satelife');
        }

        // Check if user can edit this event
        $userRole = $this->getUserRole();
        $userId = $_SESSION['user_id'];
        
        if ($userRole === ROLE_COACH) {
            // Coaches can only edit their own events
            if ($event['coach_id'] != $userId) {
                flash('You can only edit your own satelife events.', 'danger');
                $this->redirect('/events/satelife');
            }
        } elseif ($userRole !== ROLE_SUPER_ADMIN && $userRole !== ROLE_PASTOR && $event['created_by'] != $userId) {
            flash('You can only edit events you created.', 'danger');
            $this->redirect('/events/satelife');
        }

        $title = trim($_POST['title'] ?? '');
        $eventDate = trim($_POST['event_date'] ?? '');
        $eventTime = trim($_POST['event_time'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $churchId = (int)($_POST['church_id'] ?? 0);
        $coachId = (int)($_POST['coach_id'] ?? 0);

        if ($title === '' || $eventDate === '' || $churchId <= 0 || $coachId <= 0) {
            flash('Title, Date, Church and Coach are required.', 'danger');
            $this->redirect("/events/satelife/edit/{$id}");
        }

        $data = [
            'title' => $title,
            'description' => $description ?: null,
            'event_date' => $eventDate,
            'event_time' => $eventTime ?: null,
            'location' => $location ?: null,
            'church_id' => $churchId,
            'coach_id' => $coachId,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->eventModel->update($id, $data);

        // Update attendance
        $attendees = isset($_POST['attendees']) && is_array($_POST['attendees']) ? array_map('intval', $_POST['attendees']) : [];
        $this->attendeeModel->setAttendedUsers('satelife', $id, $attendees);

        flash('Satelife event updated successfully!', 'success');
        $this->redirect('/events/satelife');
    }

    public function delete(int $id): void
    {
        $this->requireRole(ROLE_COACH);

        $event = $this->eventModel->findById($id);
        if (!$event) {
            flash('Event not found.', 'danger');
            $this->redirect('/events/satelife');
        }

        // Check if user can delete this event
        $userRole = $this->getUserRole();
        $userId = $_SESSION['user_id'];
        
        if ($userRole === ROLE_COACH) {
            // Coaches can only delete their own events
            if ($event['coach_id'] != $userId) {
                flash('You can only delete your own satelife events.', 'danger');
                $this->redirect('/events/satelife');
            }
        } elseif ($userRole !== ROLE_SUPER_ADMIN && $userRole !== ROLE_PASTOR && $event['created_by'] != $userId) {
            flash('You can only delete events you created.', 'danger');
            $this->redirect('/events/satelife');
        }

        // Delete attendance records first
        $this->attendeeModel->deleteByEvent('satelife', $id);
        
        // Delete the event
        $this->eventModel->delete($id);

        flash('Satelife event deleted successfully!', 'success');
        $this->redirect('/events/satelife');
    }

    public function show(int $id): void
    {
        $this->requireRole(ROLE_COACH);
        
        $event = $this->eventModel->getEventWithDetails($id);
        if (!$event) {
            flash('Event not found.', 'danger');
            $this->redirect('/events/satelife');
        }

        $this->view('events/satelife/view', [
            'title' => 'View Satelife Event',
            'event' => $event,
            'attendees' => $this->attendeeModel->getAttendeeUsers('satelife', $id)
        ]);
    }

    public function duplicate(int $id): void
    {
        $this->requireRole(ROLE_COACH);

        $event = $this->eventModel->findById($id);
        if (!$event) {
            flash('Event not found.', 'danger');
            $this->redirect('/events/satelife');
        }

        // Check if user can duplicate this event
        $userRole = $this->getUserRole();
        $userId = $_SESSION['user_id'];
        
        if ($userRole === ROLE_COACH) {
            // Coaches can only duplicate their own events
            if ($event['coach_id'] != $userId) {
                flash('You can only duplicate your own satelife events.', 'danger');
                $this->redirect('/events/satelife');
            }
        } elseif ($userRole !== ROLE_SUPER_ADMIN && $userRole !== ROLE_PASTOR && $event['created_by'] != $userId) {
            flash('You can only duplicate events you created.', 'danger');
            $this->redirect('/events/satelife');
        }

        // Create a copy of the event
        $data = [
            'title' => $event['title'] . ' (Copy)',
            'description' => $event['description'],
            'event_date' => $event['event_date'],
            'event_time' => $event['event_time'],
            'location' => $event['location'],
            'church_id' => $event['church_id'],
            'coach_id' => $event['coach_id'],
            'created_by' => (int)$_SESSION['user_id'],
            'status' => 'active',
        ];

        $newEventId = $this->eventModel->create($data);

        flash('Satelife event duplicated successfully!', 'success');
        $this->redirect('/events/satelife');
    }

    private function getMembersForCurrentCoach(): array
    {
        $userRole = $this->getUserRole();
        $userId = $_SESSION['user_id'];
        $churchId = $_SESSION['church_id'] ?? null;

        if ($userRole === ROLE_SUPER_ADMIN) {
            // Super admin can see all members
            return $this->memberModel->getAllMembersWithHierarchy();
        } elseif ($userRole === ROLE_PASTOR) {
            // Pastor can see all members in their church
            return $this->memberModel->getMembersByChurch($churchId);
        } else {
            // Coach can see members under them
            return $this->memberModel->getMembersByCoach($userId);
        }
    }

    private function getMembersForCoach(?int $coachId): array
    {
        if (!$coachId) {
            return [];
        }
        return $this->memberModel->getMembersByCoach($coachId);
    }

    private function getCoachesForChurch(?int $churchId): array
    {
        if (!$churchId) {
            return [];
        }
        return $this->memberModel->getCoachesByChurch($churchId);
    }
    
    private function getAllCoaches(): array
    {
        return $this->coachModel->getAllCoaches();
    }

    // AJAX endpoint for dynamic member loading
    public function getMembersByCoach(): void
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $coachId = (int)($_POST['coach_id'] ?? 0);
        if (!$coachId) {
            echo json_encode(['error' => 'Coach ID is required']);
            return;
        }

        $members = $this->memberModel->getMembersByCoach($coachId);
        header('Content-Type: application/json');
        echo json_encode($members);
    }

    // AJAX endpoint for member search
    public function searchMembers(): void
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $searchTerm = trim($_POST['search'] ?? '');
        $churchId = (int)($_POST['church_id'] ?? 0);
        $coachId = (int)($_POST['coach_id'] ?? 0);

        if (strlen($searchTerm) < 2) {
            echo json_encode(['error' => 'Search term must be at least 2 characters']);
            return;
        }

        $userRole = $this->getUserRole();
        $userId = $_SESSION['user_id'];
        $userChurchId = $_SESSION['church_id'] ?? null;

        // If a specific coach is selected, search only under that coach
        if ($coachId > 0) {
            $results = $this->memberModel->searchMembersByCoach($searchTerm, $coachId);
        } else {
            // Determine search scope based on user role
            if ($userRole === ROLE_SUPER_ADMIN) {
                // Super admin can search all members
                $results = $this->memberModel->searchMembers($searchTerm);
            } elseif ($userRole === ROLE_PASTOR) {
                // Pastor can search members in their church
                $results = $this->memberModel->searchMembersByChurch($searchTerm, $userChurchId);
            } else {
                // Coach can search members under them and mentors
                $results = $this->memberModel->searchMembersByCoach($searchTerm, $userId);
            }
        }

        header('Content-Type: application/json');
        echo json_encode($results);
    }

    // AJAX endpoint for adding new member to satelife
    public function addMemberToSatelife(): void
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $churchId = (int)($_POST['church_id'] ?? 0);
        $coachId = (int)($_POST['coach_id'] ?? 0);

        if (empty($name) || !$churchId || !$coachId) {
            echo json_encode(['error' => 'Name, church, and coach are required']);
            return;
        }

        // Check if member already exists (only if a real email was provided)
        if (!empty($_POST['email'])) {
            $existingMember = $this->memberModel->findByEmail($email);
            if ($existingMember) {
                echo json_encode(['error' => 'Member with this email already exists']);
                return;
            }
        }

        try {
            // Generate placeholder email if none provided
            if (empty($email)) {
                $timestamp = time();
                $random = rand(1000, 9999);
                $email = "no-email-{$timestamp}-{$random}@placeholder.local";
            }
            
            // Create new member with pending status
            $memberData = [
                'name' => $name,
                'email' => $email,
                'church_id' => $churchId,
                'role' => 'member',
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $memberId = $this->memberModel->create($memberData);

            // Add to hierarchy under the coach
            $this->memberModel->addToHierarchy($memberId, $coachId);

            $newMember = $this->memberModel->findById($memberId);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Member added to satelife successfully',
                'member' => $newMember
            ]);
        } catch (Exception $e) {
            echo json_encode(['error' => 'Failed to add member: ' . $e->getMessage()]);
        }
    }

    // AJAX endpoint for loading coaches by church
    public function getCoachesByChurch(): void
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $churchId = (int)($_POST['church_id'] ?? 0);

        if (!$churchId) {
            echo json_encode(['error' => 'Church ID is required']);
            return;
        }

        $userRole = $this->getUserRole();
        $userChurchId = $_SESSION['church_id'] ?? null;

        // Check permissions
        if ($userRole === ROLE_SUPER_ADMIN) {
            // Super admin can see coaches from any church
            $coaches = $this->memberModel->getCoachesByChurch($churchId);
        } elseif ($userRole === ROLE_PASTOR) {
            // Pastor can only see coaches from their church
            if ($userChurchId != $churchId) {
                echo json_encode(['error' => 'You can only view coaches from your church']);
                return;
            }
            $coaches = $this->memberModel->getCoachesByChurch($churchId);
        } else {
            // Coach can only see coaches from their church
            if ($userChurchId != $churchId) {
                echo json_encode(['error' => 'You can only view coaches from your church']);
                return;
            }
            $coaches = $this->memberModel->getCoachesByChurch($churchId);
        }

        header('Content-Type: application/json');
        echo json_encode($coaches);
    }
} 