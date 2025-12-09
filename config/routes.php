<?php

use Controller\HomeController;
use Controller\UserController;

$router->add('GET', '/', [HomeController::class, 'index']);
$router->add('GET', '/login', [UserController::class, 'login']);
$oruter->add('GET', '/register', [UserController::class, 'register']);
$router->add('POST', '/login', [UserController::class, 'auth']);

?>