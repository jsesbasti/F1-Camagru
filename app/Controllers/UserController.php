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
			Helper::json_response([
				'errors' => [
					'error' => 'Invalid JSON'
				]
			], 400);
		}

		$errors = UserValidator::validateRegister($data);
		if ($errors) {
			Helper::json_response([
				'errors' => $errors
			], 400);
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
			Helper::json_response([
				'errors' => [
					'username' => 'Username already taken'
				]
			], 409);
		}
		Helper::json_response([
			'errors' => [
				'email' => 'Email in use'
			]
		], 409);
	}

	public function auth(): void
	{
		header('Content-Type: application/json');
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
		$user = User::findByUsername($data['username']);
		if (!$user || !password_verify($data['password'], $user['password'])) {
			Helper::json_response([
				'errors' => [
					'error' => 'Invalid credentials'
				]
			], 401);
		}
		Auth::authenticate($user['id']);
		Helper::json_response([
			'success' => true
		], 200);
	}
}
?>