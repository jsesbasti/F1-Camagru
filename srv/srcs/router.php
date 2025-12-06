<?php
header("Access-Control-Allow-Origin: http://localhost:8000");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
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
		if ($method === 'OPTIONS')
		{
			http_response_code(204);
			die();
		}
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