<?php
namespace App\Controllers;

use App\Models\User;
use App\Models\Verification;
use App\Core\Controller;
use App\Core\Session;
use App\Core\Helper;
use App\Core\Mailer;
use App\Validators\UserValidator;

class RegisterController extends Controller
{

	public function register()
	{
		$this->requireGuest();
		$this->view('register', [
			'title' => 'Register page',
			'scripts' => [
				'auth'
			]
		]);
	}

	public function loadVerify()
	{
		$this->view('verifypage', [
			'title' => 'Verify page',
			'pendingEmail' => $_SESSION['pending_verification_email'] ?? '',
			'scripts' => [
				'auth'
			]
		]);
	}

	public function create(): void
	{
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

		// Check first, so a taken name/email never reaches the INSERT (and never burns an id).
		$taken = [];
		if (User::find(username: $username)) {
			$taken['username'] = 'Username already taken';
		}
		if (User::find(email: $email)) {
			$taken['email'] = 'Email in use';
		}
		if ($taken) {
			Helper::json_response(['errors' => $taken], 400);
		}

		$hash = password_hash($password, PASSWORD_DEFAULT);

		if (!User::add([
			':email' => $email,
			':username' => $username,
			':password' => $hash
		])) {
			// Only reachable if someone registered the same name/email in the meantime.
			Helper::json_response(['errors' => ['error' => 'Username or email already in use']], 400);
		}

		self::sendEmailVerification($username);
		$_SESSION['pending_verification_email'] = $email;
		Helper::json_response(['success' => true, 'redirect' => '/register/verify']);
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
			$this->invalidToken('This verification link is not valid or has already been used. If your account is already verified, just log in.', '/login', 'Go to login');
		}
		if ($email_verification['expires_at'] < (new \DateTime())->format('Y-m-d H:i:s')) {
			$this->invalidToken('This verification link has expired.', '/register/verify', 'Send a new link');
		}
		if (Verification::verifyEmail($email_verification['user_id'], 'is_verified', 1)) {
			Verification::deleteByToken($token);
		}

		// Aquí buscarías el usuario por el token en la base de datos,
		// marcarías verified = 1, borrarías el token de la DB y redirigirías a /login con un mensaje de éxito.

		$this->view('login', [
			'title' => 'Login page',
			'scripts' => [
				'auth'
			]
		]);
	}

	public function resend(): void
	{
		$data = json_decode(file_get_contents("php://input"), true) ?: [];
		$email = trim((string) ($data['email'] ?? ''));

		try {
			UserValidator::validateEmail($email);
		} catch (\Exception $e) {
			Helper::json_response(['errors' => ['email' => $e->getMessage()]], 400);
		}

		$user = User::find(email: $email);
		if ($user && !$user['is_verified']) {
			// Tokens last 30 min, so one expiring in more than 29 was sent under a minute ago.
			$last = Verification::find(id: (int) $user['id']);
			$tooSoon = $last && $last['expires_at'] > (new \DateTime())->modify('+29 minutes')->format('Y-m-d H:i:s');
			if (!$tooSoon) {
				self::sendEmailVerification($user['username']);
			}
		}

		// Same answer either way, so this can't be used to find out which emails are registered.
		Helper::json_response([
			'success' => true,
			'message' => 'If that email belongs to an account waiting for verification, a new link is on its way.'
		]);
	}

	private function invalidToken(string $message, string $linkHref, string $linkText): void
	{
		http_response_code(403);
		$this->view('email-confirm', [
			'title' => 'Verification link',
			'ok' => false,
			'heading' => 'Link not valid',
			'message' => $message,
			'linkHref' => $linkHref,
			'linkText' => $linkText
		]);
		exit;
	}

	private function sendEmailVerification($username) {
		$token = bin2hex(random_bytes(16));
		$user = Verification::find(username: $username);
		$expires_at = (new \DateTime())->modify('+30 minutes')->format('Y-m-d H:i:s');
		$url = Mailer::baseUrl() . "/verify?token=$token";
		if ($user == null) {
			Helper::json_response([
				'errors' => [
					'error' => 'User does not exist'
				]
			], 403);
			exit ;
		}
		if (Verification::add([":user_id" => $user['id'], ":token" => $token, ":expires_at" => $expires_at]) != null) {
			Mailer::send($user['email'], 'EMAIL VERIFICATION', "You need to verify your account $username.\r\nHere is the link where you can do it.\r\n\r\n$url");
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
