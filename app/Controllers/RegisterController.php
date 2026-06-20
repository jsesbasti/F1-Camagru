<?php
namespace App\Controllers;

use App\Models\User;
use App\Core\Controller;
use App\Core\Auth;
use App\Validators\UserValidator;
use App\Core\Helper;

class RegisterController extends Controller
{

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

		// NOTA: Para la verificación por email:
		// 1. Aquí se generaría un token de activación único, ej: bin2hex(random_bytes(16))
		// 2. Se guardaría el usuario con verified = 0 y el token en tu DB.
		// 3. Se enviaría un email con un enlace de verificación, ej: http://localhost:8080/verify?token=XYZ

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
			exit;
		}
		Helper::json_response([
			'errors' => [
				'email' => 'Email in use'
			]
		], 409);
		exit;
	}

	public function verify()
	{
		// Método para manejar la futura verificación de email mediante token (?token=...)
		$token = $_GET['token'] ?? null;
		if (!$token) {
			header('Location: /register');
			exit;
		}

		// Aquí buscarías el usuario por el token en la base de datos,
		// marcarías verified = 1, borrarías el token de la DB y redirigirías a /login con un mensaje de éxito.

		$this->view('login', [
			'title' => 'Login page',
			'msg' => 'Cuenta verificada con éxito. Ya puedes iniciar sesión.'
		]);
	}
}
