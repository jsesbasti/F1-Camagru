<?php
namespace App\Controllers;

use App\Models\User;
use App\Core\Controller;
use App\Core\Auth;
use App\Validators\UserValidator;
use App\Core\Helper;

class UserController extends Controller
{
	private $session;

	public function register()
	{
		if (Auth::check()) {
			header('Location: /', true, 301);
			exit;
		}
		$this->view('register', [
			'title' => 'Register page',
		]);
	}

	public function login()
	{
		if (Auth::check()) {
			header('Location: /', true, 301);
			exit;
		}
		$this->view('login', [
			'title' => 'Login page',
		]);
	}

	public function logout(): void
	{
		Auth::logout();
		header('Location: /', true, 301);
		exit;
	}

	public function create(): void
	{
		header('Content-Type: application/json; charset=utf-8');
		$data = json_decode(file_get_contents("php://input"), true);
		if (!$data) {
			http_response_code(400);
			echo json_encode([
				'errors' => [
					'error' => 'Invalid JSON'
				]
			]);
			exit;
		}

		$errors = UserValidator::validateRegister($data);
		if ($errors) {
			http_response_code(400);
			echo json_encode([
				'errors' => $errors
			]);
			exit;
		}

		$email = trim($data['email'] ?? '');
		$username = trim($data['username'] ?? '');
		$password = trim($data['password'] ?? '');

		$hash = password_hash($password, PASSWORD_DEFAULT);
		if (
			User::add([
				':email' => $email,
				':username' => $username,
				':password' => $hash
			])
		) {
			Helper::json_response(['success' => true]);
			exit;
		}
		if (User::findByUsername($username) != null) {
			http_response_code(409);
			echo json_encode([
				'errors' => [
					'username' => 'Username already taken'
				]
			]);
			exit;
		}
		http_response_code(409);
		echo json_encode([
			'errors' => [
				'email' => 'Email in use'
			]
		]);
		exit;
	}

	public function auth(): void
	{
		header('Content-Type: application/json');
		$data = json_decode(file_get_contents("php://input"), true);
		if (!$data) {
			http_response_code(400);
			echo json_encode([
				'errors' => [
					'error' => 'Invalid JSON'
				]
			]);
			exit;
		}
		if (
			empty($data['username']) ||
			empty($data['password'])
		) {
			http_response_code(400);
			echo json_encode([
				'errors' => [
					'error' => 'Missing credentials'
				]
			]);
			exit;
		}
		$user = User::findByUsername($data['username']);
		if (!$user || !password_verify($data['password'], $user['password'])) {
			http_response_code(401);
			echo json_encode([
				'errors' => [
					'error' => 'Invalid credentials'
				]
			]);
			exit;
		}
		Auth::authenticate($user['id']);
		http_response_code(200);
		echo json_encode([
			'success' => true
		]);
		exit;
	}
}
?>