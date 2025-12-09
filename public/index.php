<?php

require_once __DIR__ . '/../vendor/autoload.php';



use App\Core\Router;
use App\Controllers\HomeController;

$router = new Router();

$router->get(path: '/', action: [HomeController::class, 'index']);

$router->run();