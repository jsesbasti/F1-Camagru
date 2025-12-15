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

	public function create(): void {
		parse_str(file_get_contents("php://input"),  $data);

		if (empty($data) || empty($data['email']) || empty($data['username']) || empty($data['password'])) {
			http_response_code(400);
		}
		$users = User::all();
		extract($data);
		foreach ($users as $user) {
			if ($user->email == $email) {
				echo 'email already in use';
			}
			if ($user->username == $username) {
				echo 'username already in use';
			}
		}
		if (User::add(array(':email' => $email, ':username' => $username, ':password' => $password))) {
			http_response_code(201);
		} else {
			http_response_code(500);
		}
	}
}
?>