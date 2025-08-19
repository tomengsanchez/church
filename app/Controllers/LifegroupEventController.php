<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\LifegroupEventModel;
use App\Models\ChurchModel;
use App\Models\LifegroupModel;

class LifegroupEventController extends Controller
{
    private LifegroupEventModel $eventModel;
    private ChurchModel $churchModel;
    private LifegroupModel $lifegroupModel;
    
    public function __construct()
    {
        $this->eventModel = new LifegroupEventModel();
        $this->churchModel = new ChurchModel();
        $this->lifegroupModel = new LifegroupModel();
    }
    
    public function index(): void
    {
        // Allow any authenticated role to access lifegroup events
        $this->requireRole(ROLE_MEMBER);
        
        $userRole = $this->getUserRole();
        $userId = $_SESSION['user_id'];
        $churchId = $_SESSION['church_id'] ?? null;
        
        $events = [];
        
        if ($userRole === ROLE_SUPER_ADMIN) {
            // Super admin can see all lifegroup events
            $events = $this->eventModel->getAllEvents();
        } else {
            // Other roles can see events they created
            $events = $this->eventModel->getEventsByMentor($userId);
        }
        
        $this->view('events/lifegroup/index', [
            'title' => 'Lifegroup Events',
            'events' => $events,
            'churches' => $this->churchModel->getAllChurches()
        ]);
    }

    public function create(): void
    {
        $this->requireRole(ROLE_MEMBER);
        $this->view('events/lifegroup/create', [
            'title' => 'Add Lifegroup Event',
            'churches' => $this->churchModel->getAllChurches(),
            'lifegroups' => $this->getLifegroupsForCurrentUser(),
            'defaultChurchId' => $_SESSION['church_id'] ?? null,
        ]);
    }

    public function store(): void
    {
        $this->requireRole(ROLE_MEMBER);

        $title = trim($_POST['title'] ?? '');
        $eventDate = trim($_POST['event_date'] ?? '');
        $eventTime = trim($_POST['event_time'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $churchId = (int)($_POST['church_id'] ?? 0);
        $lifegroupId = (int)($_POST['lifegroup_id'] ?? 0);

        if ($title === '' || $eventDate === '' || $churchId <= 0) {
            flash('Title, Date and Church are required.', 'danger');
            $this->redirect('/events/lifegroup/create');
        }

        $data = [
            'title' => $title,
            'description' => $description ?: null,
            'event_date' => $eventDate,
            'event_time' => $eventTime ?: null,
            'location' => $location ?: null,
            'church_id' => $churchId,
            'created_by' => (int)$_SESSION['user_id'],
            'status' => 'active',
        ];

        // If schema supports lifegroup_id, include it
        if ($lifegroupId > 0 && $this->eventModel->hasColumn('lifegroup_id')) {
            $data['lifegroup_id'] = $lifegroupId;
        }

        $this->eventModel->create($data);
        flash('Lifegroup event created successfully.', 'success');
        $this->redirect('/events/lifegroup');
    }

    public function edit(int $id): void
    {
        $this->requireRole(ROLE_MEMBER);
        $event = $this->eventModel->findById($id);
        if (!$event) {
            $this->redirect('/events/lifegroup');
        }
        // Only allow owner or super admin to edit
        if ($this->getUserRole() !== ROLE_SUPER_ADMIN && (int)$event['created_by'] !== (int)$_SESSION['user_id']) {
            $this->redirect('/events/lifegroup');
        }

        $this->view('events/lifegroup/edit', [
            'title' => 'Edit Lifegroup Event',
            'event' => $event,
            'churches' => $this->churchModel->getAllChurches(),
            'lifegroups' => $this->getLifegroupsForCurrentUser(),
            'defaultChurchId' => $_SESSION['church_id'] ?? null,
        ]);
    }

    public function update(int $id): void
    {
        $this->requireRole(ROLE_MEMBER);
        $event = $this->eventModel->findById($id);
        if (!$event) {
            $this->redirect('/events/lifegroup');
        }
        if ($this->getUserRole() !== ROLE_SUPER_ADMIN && (int)$event['created_by'] !== (int)$_SESSION['user_id']) {
            $this->redirect('/events/lifegroup');
        }

        $title = trim($_POST['title'] ?? '');
        $eventDate = trim($_POST['event_date'] ?? '');
        $eventTime = trim($_POST['event_time'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $churchId = (int)($_POST['church_id'] ?? 0);
        $lifegroupId = (int)($_POST['lifegroup_id'] ?? 0);

        if ($title === '' || $eventDate === '' || $churchId <= 0) {
            flash('Title, Date and Church are required.', 'danger');
            $this->redirect('/events/lifegroup/edit/' . $id);
        }

        $data = [
            'title' => $title,
            'description' => $description ?: null,
            'event_date' => $eventDate,
            'event_time' => $eventTime ?: null,
            'location' => $location ?: null,
            'church_id' => $churchId,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($lifegroupId > 0 && $this->eventModel->hasColumn('lifegroup_id')) {
            $data['lifegroup_id'] = $lifegroupId;
        }

        $this->eventModel->update($id, $data);
        flash('Lifegroup event updated successfully.', 'success');
        $this->redirect('/events/lifegroup');
    }

    public function delete(int $id): void
    {
        $this->requireRole(ROLE_MEMBER);
        $event = $this->eventModel->findById($id);
        if ($event) {
            if ($this->getUserRole() === ROLE_SUPER_ADMIN || (int)$event['created_by'] === (int)$_SESSION['user_id']) {
                $this->eventModel->delete($id);
                flash('Lifegroup event deleted.', 'success');
            }
        }
        $this->redirect('/events/lifegroup');
    }

    public function show(int $id): void
    {
        $this->requireRole(ROLE_MEMBER);
        $event = $this->eventModel->getEventWithDetails($id);
        if (!$event) {
            $this->redirect('/events/lifegroup');
        }
        $this->view('events/lifegroup/view', [
            'title' => 'Lifegroup Event Details',
            'event' => $event,
        ]);
    }

    public function duplicate(int $id): void
    {
        $this->requireRole(ROLE_MEMBER);
        $event = $this->eventModel->findById($id);
        if (!$event) {
            $this->redirect('/events/lifegroup');
        }
        // Any authenticated role can duplicate; new event is owned by current user
        $nextWeekDate = date('Y-m-d', strtotime($event['event_date'] . ' +7 days'));
        $newData = [
            'title' => $event['title'],
            'description' => $event['description'],
            'event_date' => $nextWeekDate,
            'event_time' => $event['event_time'],
            'location' => $event['location'],
            'church_id' => (int)$event['church_id'],
            'created_by' => (int)$_SESSION['user_id'],
            'status' => 'active',
        ];
        if ($this->eventModel->hasColumn('lifegroup_id') && !empty($event['lifegroup_id'])) {
            $newData['lifegroup_id'] = (int)$event['lifegroup_id'];
        }
        $newId = $this->eventModel->create($newData);
        flash('Event duplicated for next week.', 'success');
        $this->redirect('/events/lifegroup');
    }

    private function getLifegroupsForCurrentUser(): array
    {
        $userRole = $this->getUserRole();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $churchId = (int)($_SESSION['church_id'] ?? 0);

        if ($userRole === ROLE_SUPER_ADMIN) {
            // For simplicity, list lifegroups in current church if set; otherwise all
            if ($churchId > 0) {
                return $this->lifegroupModel->getLifegroupsByChurch($churchId);
            }
            return $this->lifegroupModel->getAllLifegroups();
        }
        if ($userRole === ROLE_PASTOR) {
            return $this->lifegroupModel->getLifegroupsByChurch($churchId);
        }
        if ($userRole === ROLE_COACH) {
            return $this->lifegroupModel->getLifegroupsByCoach($userId);
        }
        if ($userRole === ROLE_MENTOR) {
            return $this->lifegroupModel->getLifegroupsByMentor($userId);
        }
        // Members: list lifegroups in their church
        if ($churchId > 0) {
            return $this->lifegroupModel->getLifegroupsByChurch($churchId);
        }
        return [];
    }
} 