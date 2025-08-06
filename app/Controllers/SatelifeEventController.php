<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\SatelifeEventModel;
use App\Models\ChurchModel;

class SatelifeEventController extends Controller
{
    private SatelifeEventModel $eventModel;
    private ChurchModel $churchModel;
    
    public function __construct()
    {
        $this->eventModel = new SatelifeEventModel();
        $this->churchModel = new ChurchModel();
    }
    
    public function index(): void
    {
        $this->requireRole(ROLE_COACH);
        
        $userRole = $this->getUserRole();
        $userId = $_SESSION['user_id'];
        $churchId = $_SESSION['church_id'] ?? null;
        
        $events = [];
        
        if ($userRole === ROLE_SUPER_ADMIN) {
            // Super admin can see all satelife events
            $events = $this->eventModel->getAllEvents();
        } else {
            // Coaches can only see events they created
            $events = $this->eventModel->getEventsByCoach($userId);
        }
        
        $this->view('events/satelife/index', [
            'title' => 'Satelife Events',
            'events' => $events,
            'churches' => $this->churchModel->getAllChurches()
        ]);
    }
} 