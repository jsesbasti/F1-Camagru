<?php

use App\Controllers\HomeController;
use App\Controllers\PostController;
use App\Controllers\LoginController;
use App\Controllers\PhotoController;
use App\Controllers\RegisterController;
use App\Controllers\UserController;
use App\Controllers\PasswordController;
use App\Controllers\SettingsController;

$router->get('/', [HomeController::class, 'index']);
$router->get('/api/posts', [PostController::class, 'index']);
$router->get('/post', [PostController::class, 'show']);
$router->post('/api/posts/update', [PostController::class, 'update']);
$router->post('/api/posts/delete', [PostController::class, 'delete']);
$router->post('/api/posts/like', [PostController::class, 'like']);
$router->post('/api/posts/comment', [PostController::class, 'comment']);
$router->get('/photo', [PhotoController::class, 'index']);
$router->post('/photo/draft', [PhotoController::class, 'saveDraft']);
$router->post('/photo/publish', [PhotoController::class, 'publish']);
$router->get('/login', [LoginController::class, 'login']);
$router->get('/register', [RegisterController::class, 'register']);
$router->post('/login', [LoginController::class, 'auth']);
$router->post('/register', [RegisterController::class, 'create']);
$router->get('/verify', [RegisterController::class, 'verify']);
$router->get('/logout', [LoginController::class, 'logout']);
$router->get('/register/verify', [RegisterController::class, 'loadVerify']);
$router->get('/profile', [UserController::class, 'profile']);
$router->get('/settings', [SettingsController::class, 'index']);
$router->post('/api/settings/username', [SettingsController::class, 'updateUsername']);
$router->post('/api/settings/email', [SettingsController::class, 'updateEmail']);
$router->get('/settings/confirm-email', [SettingsController::class, 'confirmEmail']);
$router->post('/api/settings/password', [SettingsController::class, 'updatePassword']);
$router->post('/api/settings/notifications', [SettingsController::class, 'updateNotifications']);
$router->post('/api/settings/avatar', [SettingsController::class, 'updateAvatar']);
$router->post('/api/settings/avatar/delete', [SettingsController::class, 'removeAvatar']);
$router->post('/api/settings/delete', [SettingsController::class, 'deleteAccount']);
$router->get('/forgot-password', [PasswordController::class, 'forgotPassword']);
$router->post('/forgot-password', [PasswordController::class, 'sendReset']);
$router->get('/reset-password', [PasswordController::class, 'loadReset']);
$router->post('/reset-password', [PasswordController::class, 'updatePassword']);

?>