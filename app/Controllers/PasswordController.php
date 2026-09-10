<?php
namespace App\Controllers;

use App\Models\User;
use App\Models\PasswordReset;
use App\Core\Controller;
use App\Core\Helper;
use App\Validators\UserValidator;

class PasswordController extends Controller
{

	public function forgotPassword()
	{
		$this->requireGuest();
		$this->view('forgot-password', [
			'title' => 'Forgot password',
			'scripts' => [
				'auth'
			]
		]);
	}

	public function sendReset(): void
	{
		$data = json_decode(file_get_contents("php://input"), true);
		if (!$data) {
			Helper::json_response([
				'errors' => [
					'error' => 'Invalid JSON'
				]
			], 400);
		}

		$email = trim($data['email'] ?? '');

		try {
			UserValidator::validateEmail($email);
		} catch (\Exception $e) {
			Helper::json_response([
				'errors' => [
					'email' => $e->getMessage()
				]
			], 400);
			exit;
		}

		$user = User::find(email: $email);
		if ($user) {
			self::sendResetEmail($user);
		}

		// Same response whether the email exists or not, so a caller can't
		// use this endpoint to check which emails are registered.
		Helper::json_response([
			'success' => true,
			'message' => 'If that email is registered, a reset link is on its way.'
		]);
	}

	public function loadReset()
	{
		$token = $_GET['token'] ?? null;
		if (!$token) {
			header('Location: /forgot-password');
			exit;
		}

		$reset = PasswordReset::find(token: $token);
		if (!$reset || $reset['expires_at'] < (new \DateTime())->format('Y-m-d H:i:s')) {
			header('Location: /forgot-password?expired=1');
			exit;
		}

		$this->view('reset-password', [
			'title' => 'Reset password',
			'token' => $token,
			'scripts' => [
				'auth'
			]
		]);
	}

	public function updatePassword(): void
	{
		$data = json_decode(file_get_contents("php://input"), true);
		if (!$data) {
			Helper::json_response([
				'errors' => [
					'error' => 'Invalid JSON'
				]
			], 400);
		}

		$token = trim($data['token'] ?? '');
		$password = trim($data['password'] ?? '');
		$confirm = trim($data['confirm'] ?? '');

		$reset = $token ? PasswordReset::find(token: $token) : null;
		if (!$reset || $reset['expires_at'] < (new \DateTime())->format('Y-m-d H:i:s')) {
			Helper::json_response([
				'errors' => [
					'error' => 'This reset link is invalid or has expired'
				]
			], 403);
			exit;
		}

		if ($password !== $confirm) {
			Helper::json_response([
				'errors' => [
					'confirm' => 'Passwords does not match'
				]
			], 400);
			exit;
		}

		try {
			UserValidator::validatePassword($password);
		} catch (\Exception $e) {
			Helper::json_response([
				'errors' => [
					'password' => $e->getMessage()
				]
			], 400);
			exit;
		}

		$hash = password_hash($password, PASSWORD_DEFAULT);
		User::mod($reset['user_id'], 'password', $hash);
		PasswordReset::deleteByToken($token);

		Helper::json_response([
			'success' => true,
			'redirect' => '/login'
		]);
	}

	private function sendResetEmail(array $user): void
	{
		$token = bin2hex(random_bytes(16));
		$expires_at = (new \DateTime())->modify('+30 minutes')->format('Y-m-d H:i:s');
		$url = "http://localhost:8080/reset-password?token=$token";

		if (PasswordReset::add([':user_id' => $user['id'], ':token' => $token, ':expires_at' => $expires_at])) {
			mail($user['email'], 'PASSWORD RESET', "We received a request to reset your Camagru password, {$user['username']}.\r\nIf you made this request, click the link below (it expires in 30 minutes):\r\n\r\n$url\r\n\r\nIf you didn't request this, you can ignore this email.");
		}
	}
}
