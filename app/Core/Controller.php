<?php

namespace App\Core;

abstract class Controller
{
    protected function view(string $path, array $data = []): void
    {
        extract($data);
        $flash = getFlash();
        
        // Start output buffering
        ob_start();
        include "app/views/{$path}.php";
        $content = ob_get_clean();
        
        // Check if layout is specified
        if (isset($layout)) {
            include "app/views/{$layout}.php";
        } else {
            echo $content;
        }
    }
    
    protected function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }
    
    protected function json(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    protected function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']);
    }
    
    protected function getUserRole(): ?string
    {
        return $_SESSION['user_role'] ?? null;
    }
    
    protected function hasPermission(string $requiredRole): bool
    {
        $userRole = $this->getUserRole();
        $roleHierarchy = [
            ROLE_SUPER_ADMIN => 5,
            ROLE_PASTOR => 4,
            ROLE_COACH => 3,
            ROLE_MENTOR => 2,
            ROLE_MEMBER => 1
        ];
        
        return isset($roleHierarchy[$userRole]) && 
               isset($roleHierarchy[$requiredRole]) && 
               $roleHierarchy[$userRole] >= $roleHierarchy[$requiredRole];
    }
    
    protected function requireAuth(): void
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/auth/login');
        }
    }
    
    protected function requireRole(string $role): void
    {
        $this->requireAuth();
        if (!$this->hasPermission($role)) {
            $this->redirect('/dashboard');
        }
    }
} 