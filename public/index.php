<?php

require_once __DIR__ . '/../vendor/autoload.php';



use App\Core\Router;
use App\Controllers\HomeController;

if (!class_exists(App\Controllers\HomeController::class)) {
    die('La clase HomeController NO existe o no está cargada.');
} else {
    echo 'La clase HomeController está correctamente cargada.';
}

$router = new Router();

$router->get(path: '/', action: [HomeController::class, 'index']);

$router->run();