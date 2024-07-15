<?php

namespace Php\Router;

class Router
{
    private $routes = [];

    public function add($path, $handler, $methods = ['GET'])
    {
        $this->routes[$path] = ['handler' => $handler, 'methods' => $methods];
    }

    public function dispatch()
    {
        // Get the full requested path
        $requestedPath = $_SERVER['REQUEST_URI'];
        $basePath = getenv('APP_BASE_PATH'); 
        $path = parse_url($requestedPath, PHP_URL_PATH);
        $path = trim(str_replace($basePath, '', $path), '/');  // Remove base path and trim slashes


        // Match the route
        foreach ($this->routes as $route => $data) {

            // Check if the route matches the requested path
            if ($path === $route) {
                $handler = $data['handler'];
                call_user_func($handler);
                return;
            }
        }

        http_response_code(404);
        echo "404 Not Found";
    }
}
?>
