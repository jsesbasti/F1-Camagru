<?php
namespace App\Controllers;

use App\Models\User;
use App\Core\Controller;
use App\Core\Session;
use App\Core\Helper;

class LoginController extends Controller {

	public function login() {
		$this->requireGuest();
		$this->view('login', [
			'title' => 'Login page',
			'scripts' => [
				'auth'
			],
		]);
	}

	public function auth(): void {
		$data = json_decode(file_get_contents("php://input"), true);
		if (!$data) {
			Helper::json_response([
				'errors' => [
					'error' => 'Invalid JSON'
				]
			], 400);
		}
		if (
			empty($data['username']) ||
			empty($data['password'])
		) {
			Helper::json_response([
				'errors' => [
					'error' => 'Missing credentials'
				]
			], 400);
		}

		$username = trim($data['username'] ?? '');
		$password = trim($data['password'] ?? '');
		$user = User::find(username: $username);
		if (!$user)
			$user = User::find(email: $username);
		if (!$user || !password_verify($password, $user['password'])) {
			Helper::json_response([
				'errors' => [
					'error' => 'Invalid credentials'
				]
			], 401);
		}

		if ($user['is_verified'] != 1) {
			Helper::json_response([
				'errors' => [
					'error' => 'Email is not verified.'
				]
			], 403);
		}

		// NOTA: Para el requisito de verificación por email:
		// Aquí deberías comprobar si el usuario tiene verified = 1 en la base de datos.
		// Si no está verificado, debes impedir el login y avisar de que verifique su bandeja de entrada.
		
		Session::authenticate($user['id']);
		Helper::json_response([
			'success' => true,
			'redirect' => '/'
		], 200);
	}

	public function logout() {
		Session::close();
		header('Location: /login');
		exit;
	}
}
