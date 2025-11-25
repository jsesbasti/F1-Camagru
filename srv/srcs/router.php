<?php

class Router {
	private array $routes = [];

	public function get($path, callable|array $callback): void {
		$this->routes['GET'][$path] = $callback;
	}
	public function post($path, callable|array $callback): void {
		$this->routes['POST'][$path] = $callback;
	}

	public function resolve(): mixed {
		$method = $_SERVER["REQUEST_METHOD"];
		$path = $_SERVER["REQUEST_URI"] ?? '/';
		$path = explode('?', $path)[0];

		$callback = $this->routes[$method][$path] ?? null;

		if ($callback === null) {
			http_response_code(404);
			return "404 not found";
		}

		if (is_array($callback)) {
			[$class, $method] = $callback;
			$controller = new $class();
			return ($controller->method);
		}

		return  $callback;
	}
}

?>