<?php

namespace Jobdating\Core;

class Router
{
    private static array $routes = [];

    /**
     * @var Router|null $router Singleton instance of the Router.
     */
    private static ?Router $router = null;

    /**
     * Private constructor to prevent direct instantiation.
     */
    private function __construct() {}

    /**
     * Get the singleton instance of the Router.
     * 
     * @return Router The singleton instance of the Router.
     */
    public static function getRouter(): Router
    {
        if (!isset(self::$router)) {

            self::$router = new Router();
        }

        return self::$router;
    }
    private function register(string $route, string $method, array|callable $action)
    {
        // Trim slashes but keep root route as empty string
        $route = trim($route, '/');
        if ($route === '/') {
            $route = '';
        }

        // Assign action to the passed route
        self::$routes[$method][$route] = $action;
    }

    public function get(string $route, array|callable $action)
    {
        $this->register($route, 'GET', $action);
    }
    public function post(string $route, array|callable $action)
    {
        $this->register($route, 'POST', $action);
    }
    public function put(string $route, array|callable $action)
    {
        $this->register($route, 'PUT', $action);
    }
    public function delete(string $route, array|callable $action)
    {
        $this->register($route, 'DELETE', $action);
    }
    public function getRoutes(): array
    {
        return self::$routes;
    }
    
    public function dispatch(?string $uri = null, ?string $method = null)
    {
        // Get the requested route.
        $requestUri = $uri ?? $_SERVER['REQUEST_URI'] ?? '/';
        
        // Remove query string from URI
        if (($pos = strpos($requestUri, '?')) !== false) {
            $requestUri = substr($requestUri, 0, $pos);
        }
        
        $requestedRoute = trim($requestUri, '/');
        // Keep empty string for root route to match registration

        $requestMethod = $method ?? $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $routes = self::$routes[$requestMethod] ?? [];
        
        // Debug output (remove in production)
        if (php_sapi_name() !== 'cli') {
            error_log("Processed route: '$requestedRoute', Method: '$requestMethod'");
            error_log("Available routes: " . json_encode(array_keys($routes)));
        }

        if (empty($routes)) {
            return $this->abort('404 Page not found');
        }

        foreach ($routes as $route => $action)
        {
            // Transform route to regex pattern.
            $routeRegex = preg_replace_callback('/{\w+(:([^}]+))?}/', function ($matches)
            {
                return isset($matches[1]) ? '(' . $matches[2] . ')' : '([a-zA-Z0-9_-]+)';
            }, $route);

            // Add the start and end delimiters.
            $routeRegex = '@^' . $routeRegex . '$@';

            // Check if the requested route matches the current route pattern.
            if (preg_match($routeRegex, $requestedRoute, $matches))
            {
                // Get all user requested path params values after removing the first matches.
                array_shift($matches);
                $routeParamsValues = $matches;

                // Find all route params names from route and save in $routeParamsNames
                $routeParamsNames = [];
                if (preg_match_all('/{(\w+)(:[^}]+)?}/', $route, $matches))
                {
                    $routeParamsNames = $matches[1];
                }

                // Combine between route parameter names and user provided parameter values.
                $routeParams = array_combine($routeParamsNames, $routeParamsValues);

                return $this->resolveAction($action, $routeParams);
            }
        }
        return $this->abort('404 Page not found');
    }
    private function resolveAction($action, $routeParams)
    {
        if (is_callable($action))
        {
            return call_user_func_array($action, $routeParams);
        } elseif (is_array($action)) {
            if (class_exists($action[0]) && method_exists($action[0], $action[1])) {
                return call_user_func_array([new $action[0], $action[1]], $routeParams);
            }
            throw new \Exception("Invalid route handler: Class or method not found");
        }
        
        throw new \Exception("Invalid route handler");
    }
    private function abort(string $message, int $code = 404)
    {
        http_response_code($code);
        echo $message;
        exit();
    }
}
