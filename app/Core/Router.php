<?php
/**
 * Router - URL Routing and Dispatch
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $namedRoutes = [];
    private array $middleware = [];
    private string $prefix = '';
    private array $groupMiddleware = [];

    public function get(string $path, $handler, ?string $name = null): self
    {
        return $this->addRoute('GET', $path, $handler, $name);
    }

    public function post(string $path, $handler, ?string $name = null): self
    {
        return $this->addRoute('POST', $path, $handler, $name);
    }

    public function put(string $path, $handler, ?string $name = null): self
    {
        return $this->addRoute('PUT', $path, $handler, $name);
    }

    public function patch(string $path, $handler, ?string $name = null): self
    {
        return $this->addRoute('PATCH', $path, $handler, $name);
    }

    public function delete(string $path, $handler, ?string $name = null): self
    {
        return $this->addRoute('DELETE', $path, $handler, $name);
    }

    public function any(string $path, $handler, ?string $name = null): self
    {
        foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] as $method) {
            $this->addRoute($method, $path, $handler, $name);
        }
        return $this;
    }

    public function group(array $options, callable $callback): self
    {
        $previousPrefix = $this->prefix;
        $previousMiddleware = $this->groupMiddleware;

        if (isset($options['prefix'])) {
            $this->prefix .= '/' . trim($options['prefix'], '/');
        }

        if (isset($options['middleware'])) {
            $middleware = is_array($options['middleware'])
                ? $options['middleware']
                : [$options['middleware']];
            $this->groupMiddleware = array_merge($this->groupMiddleware, $middleware);
        }

        $callback($this);

        $this->prefix = $previousPrefix;
        $this->groupMiddleware = $previousMiddleware;

        return $this;
    }

    private function addRoute(string $method, string $path, $handler, ?string $name): self
    {
        $path = $this->prefix . '/' . trim($path, '/');
        $path = '/' . trim($path, '/');

        // Convert route parameters to regex
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';

        $route = [
            'method' => $method,
            'path' => $path,
            'pattern' => $pattern,
            'handler' => $handler,
            'middleware' => $this->groupMiddleware,
            'name' => $name,
        ];

        $this->routes[] = $route;

        if ($name) {
            $this->namedRoutes[$name] = $route;
        }

        return $this;
    }

    public function middleware(string $middleware): self
    {
        if (!empty($this->routes)) {
            $lastIndex = count($this->routes) - 1;
            $this->routes[$lastIndex]['middleware'][] = $middleware;
        }
        return $this;
    }

    public function route(string $name, array $params = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new \RuntimeException("Route '$name' not found");
        }

        $path = $this->namedRoutes[$name]['path'];

        foreach ($params as $key => $value) {
            $path = str_replace("{{$key}}", (string) $value, $path);
        }

        return url($path);
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = '/' . trim($uri, '/');

        // Handle PUT/PATCH/DELETE via POST with _method field
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                // Extract named parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Run middleware
                foreach ($route['middleware'] as $middlewareClass) {
                    $middleware = new $middlewareClass();
                    $result = $middleware->handle();
                    if ($result === false) {
                        return;
                    }
                }

                // Execute handler
                $this->executeHandler($route['handler'], $params);
                return;
            }
        }

        // No route found
        $this->notFound();
    }

    private function executeHandler($handler, array $params): void
    {
        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
            return;
        }

        if (is_string($handler)) {
            [$controllerClass, $method] = explode('@', $handler);

            if (!class_exists($controllerClass)) {
                throw new \RuntimeException("Controller '$controllerClass' not found");
            }

            $controller = new $controllerClass();

            if (!method_exists($controller, $method)) {
                throw new \RuntimeException("Method '$method' not found in '$controllerClass'");
            }

            call_user_func_array([$controller, $method], $params);
            return;
        }

        if (is_array($handler)) {
            [$controllerClass, $method] = $handler;
            $controller = is_object($controllerClass) ? $controllerClass : new $controllerClass();
            call_user_func_array([$controller, $method], $params);
            return;
        }

        throw new \RuntimeException('Invalid route handler');
    }

    private function notFound(): void
    {
        http_response_code(404);

        if (isAjax()) {
            jsonResponse(['error' => 'Not Found'], 404);
        }

        include APP_PATH . '/Views/errors/404.php';
    }
}
