<?php

namespace App\Controllers;

use App\Core\Controller;

class EventController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        
        $this->view('events/index', [
            'title' => 'Events',
            'events' => []
        ]);
    }
} 