<?php

use App\Controllers\HomeController;
use App\Controllers\UserController;

$router->get('/', [HomeController::class, 'index']);
$router->get('/login', [UserController::class, 'login']);
$router->get('/logout', [UserController::class, 'logout']);
$router->get('/register', [UserController::class, 'register']);
$router->post('/login', [UserController::class, 'auth']);
$router->post('/register', [UserController::class, 'create']);

?>