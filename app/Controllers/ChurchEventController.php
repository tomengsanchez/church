<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ChurchEventModel;
use App\Models\ChurchModel;

class ChurchEventController extends Controller
{
    private ChurchEventModel $eventModel;
    private ChurchModel $churchModel;
    
    public function __construct()
    {
        $this->eventModel = new ChurchEventModel();
        $this->churchModel = new ChurchModel();
    }
    
    public function index(): void
    {
        $this->requireRole(ROLE_PASTOR);
        
        $userRole = $this->getUserRole();
        $userId = $_SESSION['user_id'];
        $churchId = $_SESSION['church_id'] ?? null;
        
        $events = [];
        
        if ($userRole === ROLE_SUPER_ADMIN) {
            // Super admin can see all church events
            $events = $this->eventModel->getAllEvents();
        } else {
            // Pastors can only see events from their church
            $events = $this->eventModel->getEventsByPastor($userId);
        }
        
        $this->view('events/church/index', [
            'title' => 'Church Events',
            'events' => $events,
            'churches' => $this->churchModel->getAllChurches()
        ]);
    }
} 