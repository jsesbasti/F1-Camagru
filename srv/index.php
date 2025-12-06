<?php
	include "./srcs/router.php";
	include "./srcs/login.php";
	include "./models/database_connection.php";
	createDB();
	$router = new Router();
	$router->add('POST', "/login", [LoginController::class, 'loginUser']);
	$router->add('POST', '/register', [LoginController::class, 'registerUser']);
	$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	$router->resolve($path);
?>