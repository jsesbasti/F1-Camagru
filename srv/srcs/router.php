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
		// echo 'hola';
		// function send_cors_headers() {
		//     $allowedOrigins = ['http://10.12.250.20:8000'];
		//     $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
		//     if (in_array($origin, $allowedOrigins, true)) {
		//         header("Access-Control-Allow-Origin: $origin");
		//         header('Access-Control-Allow-Credentials: true');
		//     }
		//     header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
		//     header('Access-Control-Allow-Headers: Content-Type, X-Requested-With, Authorization, Accept');
		// 	header("Content-Type: application/json");
		// }
		// send_cors_headers();
		// if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
		//     http_response_code(204);
		//     exit;
		// }
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