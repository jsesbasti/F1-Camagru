<?php

class Router {
	private array $routes = [];


	public function add(string $method, string $path, array $controller) {
		$path = $this->normalizePath($path);
		$this->routes[] = [
			'path' => $path,
			'method' => strtoupper($method),
			'controller' => $controller,
			'middlewares' => []
		];
	}

	private function normalizePath(string $path) {
		$path = trim($path, '/'); $path = "/{$path}/";
		$path = preg_replace('#[/]{2,}#', '/', $path);
		return $path;
	}

	public function resolve(string $path) {
		$path = $this->normalizePath($path);
		$method = strtoupper($_SERVER['REQUEST_METHOD']);
		foreach ($this->routes as $route) {
			if (!preg_match("#^{$route['path']}$#", $path) || $route['method'] !== $method) {
				continue;
			}
			[$class, $function] = $route['controller'];
			$controllerInstance = new $class;
			$controllerInstance->{$function}();
			die();
		}
		http_response_code(404);
	}
}


?>