<?php

namespace App\Core;

class Router {
    private array $routes = [];

    public function get(string $path, array|callable $handler): void {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, array|callable $handler): void {
        $this->addRoute('POST', $path, $handler);
    }

    public function any(string $path, array|callable $handler): void {
        $this->addRoute('GET', $path, $handler);
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, array|callable $handler): void {
        $path = '/' . trim($path, '/');
        // Convert route pattern {param} to regex named group
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => $pattern,
            'handler' => $handler
        ];
    }

    public function dispatch(string $uri, string $method): void {
        // Normalize multiple consecutive slashes (e.g. //login -> /login)
        $uri = preg_replace('#/+#', '/', $uri);
        
        $parsed = parse_url($uri, PHP_URL_PATH);
        if (!empty($parsed)) {
            $uri = $parsed;
        }

        // Support fallback query parameter like index.php?url=p/EMP001
        if (isset($_GET['url']) && !empty($_GET['url'])) {
            $uri = '/' . trim($_GET['url'], '/');
        }

        // Normalize leading and trailing slashes
        $uri = '/' . trim(preg_replace('#/+#', '/', (string)$uri), '/');
        $method = strtoupper($method);

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $handler = $route['handler'];

                if (is_callable($handler)) {
                    call_user_func_array($handler, $params);
                    return;
                }

                if (is_array($handler) && count($handler) === 2) {
                    [$controllerClass, $actionMethod] = $handler;
                    if (class_exists($controllerClass)) {
                        $controller = new $controllerClass();
                        if (method_exists($controller, $actionMethod)) {
                            call_user_func_array([$controller, $actionMethod], $params);
                            return;
                        }
                    }
                }
            }
        }

        // 404 Not Found
        http_response_code(404);
        if (Request::isAjax()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
            exit;
        }

        echo '<!DOCTYPE html><html><head><title>404 Not Found</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head><body class="bg-light d-flex align-items-center min-vh-100"><div class="container text-center"><h1 class="display-1 fw-bold text-primary">404</h1><h3 class="mb-4">Page or Employee Link Not Found</h3><p class="text-muted mb-4">The requested attendance URL or administration page does not exist or has been moved.</p><a href="' . base_url() . '" class="btn btn-primary px-4 py-2">Return to Home</a></div></body></html>';
        exit;
    }
}
