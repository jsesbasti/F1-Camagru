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
		$data = json_decode(file_get_contents("php://input"), true);
	}
}
?>