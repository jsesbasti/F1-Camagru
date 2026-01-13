<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class UserController extends Controller {
	private $session;

	public function register() {
		$input = [];
		$this->view('register', [
			'title' => 'Register page',
			'input' => $input
		]);
	}
	
	public function login() {
		$input = [];
		$this->view('login', [
			'title' => 'Login page',
			'input' => $input
		]);
	}

	public function create(): void {
		parse_str(file_get_contents("php://input"),  $data);

		if (empty($data) || empty($data['email']) || empty($data['username']) || empty($data['password'])) {
			http_response_code(400);
		}
		$users = User::all();
		extract($data);
		if (User::add(array(':email' => $email, ':username' => $username, ':password' => $password))) {
			http_response_code(201);
		} else {
			echo 'user or email already in use';
			http_response_code(400);
		}
	}

	public function auth(): void {
		parse_str(file_get_contents("php://input"),  $data);

		if (empty($data) || empty($data['username']) || empty($data['password'])) {
			http_response_code(400);
		}
		$users = User::all();
		extract($data);
	}
}
?>