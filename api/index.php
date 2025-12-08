<?php
	include "./router.php";
	include "./controllers/userController.php";
	include "./models/database_connection.php";
	if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
		die(http_response_code(204));
	}
	createDB();
	$router = new Router();
	$router->add('GET', "/logged", [UserController::class, 'isLogged']);
	$router->add('POST', "/login", [UserController::class, 'loginUser']);
	$router->add('POST', '/register', [UserController::class, 'registerUser']);
	$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	$router->resolve($path);
?>