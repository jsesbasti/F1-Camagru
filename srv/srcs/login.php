<?php
include "./models/response.php";
include './models/database_connection.php';
class LoginController {
	public function loginUser() {
		$input = json_decode(file_get_contents('php://input'), true);
		if (!is_array($input)) {
			json_response(['msg' => 'invalid request'], 400);
		}
		$user = $input['user'] ?? null;
		$password = $input['password'] ?? null;

		// frontend should validate, but double-check here
		if (empty($user) || empty($password)) {
			json_response(['msg' => 'invalid credentials'], 401);
		}
		// Query for user by username
		$stmt = makeQuery("SELECT * FROM users WHERE username = :username", [':username' => $user]);
		if ($stmt->rowCount() === 0) {
			json_response(['msg' => 'invalid credentials'], 401);
		}
		$dbUser = $stmt->fetch();
		// password_verify(plainPassword, hashFromDb)
		if (password_verify($password, $dbUser['password'])) {
			json_response(['msg' => 'login successful', 'user' => $dbUser['username']], 200);
		} else {
			json_response(['msg' => 'invalid credentials'], 401);
		}
	}
	public function registerUser() {
		$input = json_decode(file_get_contents('php://input'), true);
		if (!is_array($input) || empty($input['user']) || empty($input['password'])) {
			json_response(['msg' => 'invalid request'], 400);
		}
		$user = $input['user'];
		$hashed = password_hash($input['password'], PASSWORD_DEFAULT);
		// Insert new user (makeQuery should return a statement or execute the query)
		makeQuery("INSERT INTO users (username, password) VALUES (:username, :password)", [':username' => $user, ':password' => $hashed]);
		json_response(['msg' => 'registered'], 201);
	}
	public function acceptPreflight() {
		json_response(204);
		exit ();
	}
	public function testGet() {
		json_response(['msg' => 'testing'], 200);
	}
}
?>