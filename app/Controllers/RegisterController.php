<?php
namespace App\Controllers;

use App\Models\User;
use App\Models\Verification;
use App\Core\Controller;
use App\Core\Session;
use App\Core\Helper;
use App\Validators\UserValidator;

class RegisterController extends Controller
{

	public function register()
	{
		if (Session::check()) {
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
			self::sendEmailVerification($username);
			header('Location: /');
			Helper::json_response(['success' => true], 200);
			exit;
		}
		if (User::find(username: $username) != null) {
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

		$email_verification = Verification::find(token: $token);
		if (!$email_verification) {
			Helper::json_response([
				'errors' => [
					'error' => 'Token does not exist'
				]
			], 403);
			exit;
		}
		if ($email_verification['expires_at'] < (new \DateTime())->format('Y-m-d H:i:s')) {
			Helper::json_response([
				'errors' => [
					'error' => 'Token has expired'
				]
			], 403);
			exit;
		}
		if (User::verifyEmail($email_verification['user_id'], 'is_verified', 1)) {
			Verification::deleteByToken($token);
		}

		// Aquí buscarías el usuario por el token en la base de datos,
		// marcarías verified = 1, borrarías el token de la DB y redirigirías a /login con un mensaje de éxito.

		$this->view('login', [
			'title' => 'Login page',
			'msg' => 'Cuenta verificada con éxito. Ya puedes iniciar sesión.'
		]);
	}

	private function sendEmailVerification($username) {
		$token = bin2hex(random_bytes(16));
		$user = Verification::find(username: $username);
		$expires_at = (new \DateTime())->modify('+30 minutes')->format('Y-m-d H:i:s');
		$url = "http://localhost:8080/verify?token=$token";
		if ($user == null) {
			Helper::json_response([
				'errors' => [
					'error' => 'User does not exist'
				]
			], 403);
			exit ;
		}
		if (Verification::add([":user_id" => $user['id'], ":token" => $token, ":expires_at" => $expires_at]) != null) {
			mail($user['email'], 'EMAIL VERIFICATION', "You need to verify your account $username.\r\nHere is the link where you can do it.\r\n\r\n$url");
			return ;
		}
		else {
			Helper::json_response([
				'errors' => [
					'error' => 'Something went wrong'
				]
			], 500);
			exit ;
		}
	}
}
