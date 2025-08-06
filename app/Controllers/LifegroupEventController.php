<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\LifegroupEventModel;
use App\Models\ChurchModel;

class LifegroupEventController extends Controller
{
    private LifegroupEventModel $eventModel;
    private ChurchModel $churchModel;
    
    public function __construct()
    {
        $this->eventModel = new LifegroupEventModel();
        $this->churchModel = new ChurchModel();
    }
    
    public function index(): void
    {
        $this->requireRole(ROLE_MENTOR);
        
        $userRole = $this->getUserRole();
        $userId = $_SESSION['user_id'];
        $churchId = $_SESSION['church_id'] ?? null;
        
        $events = [];
        
        if ($userRole === ROLE_SUPER_ADMIN) {
            // Super admin can see all lifegroup events
            $events = $this->eventModel->getAllEvents();
        } else {
            // Mentors can only see events they created
            $events = $this->eventModel->getEventsByMentor($userId);
        }
        
        $this->view('events/lifegroup/index', [
            'title' => 'Lifegroup Events',
            'events' => $events,
            'churches' => $this->churchModel->getAllChurches()
        ]);
    }
} 