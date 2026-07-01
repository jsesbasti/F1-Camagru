<?php
namespace App\Controllers;

use App\Models\User;
use App\Core\Controller;
use App\Core\Session;
use App\Validators\UserValidator;
use App\Core\Helper;

class UserController extends Controller
{
	private $session;

	public function updateEmail() {
		header('Content-Type: application/json; charset=utf-8');
		if (Session::check()) {
			$id = Session::getId();
		}
		else {
			Helper::json_response([
				'errors' => [
					'error' => 'Session lost'
				]
			], 403);
			exit;
		}
		$data = json_decode(file_get_contents("php://input"), true);
		if (!$data) {
			Helper::json_response([
				'errors' => [
					'error' => 'Invalid JSON'
				]
			], 400);
		}

		$old_email = trim($data['old_email'] ?? '');
		$new_email = trim($data['new_email'] ?? '');
		$pass = trim($data['password'] ?? '');
		if (!User::findByEmail())
		try {
			UserValidator::validateEmail($new_email);
		} catch (\Exception $e) {
			$errors['new_email'] = $e->getMessage();
		}

		if () (
			User::mod()
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
			exit;
		}
		Helper::json_response([
			'errors' => [
				'email' => 'Email in use'
			]
		], 409);
		exit;
	}

	public function login()
	{
		if (Session::check()) {
			header('Location: /', true, 301);
			exit;
		}
		$this->view('login', [
			'title' => 'Login page',
		]);
	}

	public function logout(): void
	{
		Session::logout();
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

	public function Session(): void
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
		Session::Sessionenticate($user['id']);
		Helper::json_response([
			'success' => true
		], 200);
	}
}
?>