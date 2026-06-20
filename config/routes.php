<?php

use App\Controllers\HomeController;
use App\Controllers\LoginController;
use App\Controllers\RegisterController;

$router->get('/', [HomeController::class, 'index']);
$router->get('/login', [LoginController::class, 'login']);
$router->get('/register', [RegisterController::class, 'register']);
$router->post('/login', [LoginController::class, 'auth']);
$router->post('/register', [RegisterController::class, 'create']);
$router->get('/verify', [RegisterController::class, 'verify']);
$router->get('/logout', [LoginController::class, 'logout']);

?>