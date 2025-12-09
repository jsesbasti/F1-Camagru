<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, array $action)
    {
        $this->routes['GET'][$path] = $action;
    }

    public function post(string $path, array $action)
    {
        $this->routes['POST'][$path] = $action;
    }

    public function run()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if (isset($this->routes[$method][$uri])) {
            [$controller, $function] = $this->routes[$method][$uri];
            $controllerInstance = new $controller;
            return $controllerInstance->$function();
        }

        http_response_code(404);
        echo "404 Not Found";
    }
}
