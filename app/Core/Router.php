<?php

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $params = [];
    
    public function get(string $path, mixed $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }
    
    public function post(string $path, mixed $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }
    
    private function addRoute(string $method, string $path, mixed $handler): void
    {
        $pattern = $this->convertPathToRegex($path);
        $this->routes[$method][$pattern] = $handler;
    }
    
    private function convertPathToRegex(string $path): string
    {
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $path);
        // Handle root path specially
        if ($pattern === '/') {
            return '^$';
        }
        // Remove leading slash and convert to regex
        $pattern = ltrim($pattern, '/');
        return $pattern;
    }
    
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = trim($uri, '/');
        
        // Remove 'churchapp' from path if present
        $uri = str_replace('churchapp', '', $uri);
        $uri = trim($uri, '/');
        
        if (empty($uri)) {
            $uri = '';
        }
        
        $handler = $this->findRoute($method, $uri);
        
        if ($handler) {
            // Check if handler is a closure or array
            if (is_callable($handler)) {
                // Execute closure directly
                $params = $this->params;
                call_user_func_array($handler, $params);
            } else {
                // Handle controller method
                [$controllerClass, $method] = $handler;
                $controller = new $controllerClass();

                // Check authentication
                if (!$this->isPublicRoute($uri) && !$this->isAuthenticated()) {
                    header('Location: /auth/login');
                    exit;
                }

                // Call the controller method with parameters
                $params = $this->params;
                if (method_exists($controller, $method)) {
                    call_user_func_array([$controller, $method], $params);
                } else {
                    $this->handleError(404, 'Method not found');
                }
            }
        } else {
            $this->handleError(404, 'Route not found');
        }
    }
    
    private function findRoute(string $method, string $uri): mixed
    {
        if (!isset($this->routes[$method])) {
            return null;
        }
        
        foreach ($this->routes[$method] as $pattern => $handler) {
            if (preg_match("#^$pattern$#", $uri, $matches)) {
                array_shift($matches); // Remove the full match
                $this->params = $matches;
                return $handler;
            }
        }
        
        return null;
    }
    
    private function isPublicRoute(string $uri): bool
    {
        $publicRoutes = ['auth/login', 'auth/register', 'home', 'test-logs'];
        return in_array($uri, $publicRoutes);
    }
    
    private function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']);
    }
    
    private function handleError(int $code, string $message): void
    {
        http_response_code($code);
        if (file_exists("app/views/errors/{$code}.php")) {
            include "app/views/errors/{$code}.php";
        } else {
            echo "Error {$code}: {$message}";
        }
        exit;
    }
} 