<?php

use App\Controllers\HomeController;
use App\Controllers\PostController;
use App\Controllers\LoginController;
use App\Controllers\PhotoController;
use App\Controllers\RegisterController;
use App\Controllers\UserController;
use App\Controllers\PasswordController;

$router->get('/', [HomeController::class, 'index']);
$router->get('/api/posts', [PostController::class, 'index']);
$router->get('/post', [PostController::class, 'show']);
$router->post('/api/posts/update', [PostController::class, 'update']);
$router->post('/api/posts/delete', [PostController::class, 'delete']);
$router->get('/photo', [PhotoController::class, 'index']);
$router->post('/photo/upload', [PhotoController::class, 'uploadPhoto']);
$router->get('/login', [LoginController::class, 'login']);
$router->get('/register', [RegisterController::class, 'register']);
$router->post('/login', [LoginController::class, 'auth']);
$router->post('/register', [RegisterController::class, 'create']);
$router->get('/verify', [RegisterController::class, 'verify']);
$router->get('/logout', [LoginController::class, 'logout']);
$router->get('/register/verify', [RegisterController::class, 'loadVerify']);
$router->get('/profile', [UserController::class, 'profile']);
$router->get('/forgot-password', [PasswordController::class, 'forgotPassword']);
$router->post('/forgot-password', [PasswordController::class, 'sendReset']);
$router->get('/reset-password', [PasswordController::class, 'loadReset']);
$router->post('/reset-password', [PasswordController::class, 'updatePassword']);

?>