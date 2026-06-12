<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Core\Auth;

class UserController extends Controller {
	private $session;

	public function register() {
		if (Auth::check()) {
			header('Location: /', true, 301);
			exit;
		}
		$this->view('register', [
			'title' => 'Register page',
		]);
	}
	
	public function login() {
		if (Auth::check()) {
			header('Location: /', true, 301);
			exit;
		}
		$this->view('login', [
			'title' => 'Login page',
		]);
	}

	public function create(): void {
		header('Content-Type: application/json');
		$data = json_decode(file_get_contents("php://input"), true);
		if (
			empty($data['email']) ||
			empty($data['username']) ||
			empty($data['password'])
		) {
			http_response_code(400);
			echo json_encode([
				'error' => 'Missing fields'
			]);
			exit;
		}
		$email = $data['email'];
		$username = $data['username'];
		$password = $data['password'];
		$hash = password_hash($password, PASSWORD_DEFAULT);
		if (User::add([
			':email' => $email,
			':username' => $username,
			':password' => $hash
		])) {
			http_response_code(201);
			echo json_encode([
				'success' => true
			]);
			exit;
		}
		http_response_code(409);
		echo json_encode([
			'error' => 'User already exists'
		]);
		exit;
	}

	public function auth(): void {
		header('Content-Type: application/json');
		$data = json_decode(file_get_contents("php://input"), true);
		if (
			empty($data['username']) ||
			empty($data['password'])
		) {
			http_response_code(400);
			echo json_encode([
				'error' => 'Missing credentials'
			]);
			exit;
		}
		$user = User::findByUsername($data['username']);
		if (!$user || !password_verify($data['password'], $user['password'])) {
			http_response_code(401);
			echo json_encode([
				'error' => 'Invalid credentials'
			]);
			exit;
		}
		Auth::authenticate($user['id']);
		echo json_encode([
			'success' => true
		]);
		exit;
	}
}
?>