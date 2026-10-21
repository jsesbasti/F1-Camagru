<?php
namespace App\Controllers;

use App\Models\User;
use App\Models\Image;
use App\Models\EmailChange;
use App\Core\Controller;
use App\Core\Session;
use App\Core\Helper;
use App\Core\Mailer;
use App\Validators\UserValidator;

class SettingsController extends Controller
{
	private const AVATAR_URL = '/uploads/avatars';
	private const AVATAR_SIZE = 256;
	private const AVATAR_MAX_BYTES = 5 * 1024 * 1024;

	public function index(): void
	{
		$this->requireAuth();

		$this->view('settings', [
			'title' => 'Settings',
			'currentUser' => $user = User::find(id: Session::getId()),
			'pendingEmail' => self::pendingEmail((int) $user['id']),
			'scripts' => [
				'settings'
			]
		]);
	}

	public function updateUsername(): void
	{
		[$user, $data] = $this->authorize();

		$username = trim($data['username'] ?? '');

		try {
			UserValidator::validateUsername($username);
		} catch (\Exception $e) {
			$this->fail(['username' => $e->getMessage()]);
		}

		if ($username === $user['username']) {
			$this->fail(['username' => 'That is already your username']);
		}

		$existing = User::find(username: $username);
		if ($existing && (int) $existing['id'] !== (int) $user['id']) {
			$this->fail(['username' => 'Username already in use']);
		}

		if (!User::mod($user['id'], 'username', $username)) {
			$this->fail(['error' => 'Could not update username'], 500);
		}

		self::notify($user['email'], 'Your Camagru username was changed',
			"Hi {$username},\r\n\r\nYour Camagru username was changed from \"{$user['username']}\" to \"{$username}\".");

		Helper::json_response([
			'success' => true,
			'message' => 'Username updated',
			'username' => $username
		]);
	}

	public function updateEmail(): void
	{
		[$user, $data] = $this->authorize();

		$email = trim($data['email'] ?? '');

		try {
			UserValidator::validateEmail($email);
		} catch (\Exception $e) {
			$this->fail(['email' => $e->getMessage()]);
		}

		if (strcasecmp($email, $user['email']) === 0) {
			$this->fail(['email' => 'That is already your email']);
		}

		$existing = User::find(email: $email);
		if ($existing && (int) $existing['id'] !== (int) $user['id']) {
			$this->fail(['email' => 'Email already in use']);
		}

		$token = bin2hex(random_bytes(16));
		$saved = EmailChange::add([
			':user_id' => $user['id'],
			':new_email' => $email,
			':token' => $token,
			':expires_at' => (new \DateTime())->modify('+30 minutes')->format('Y-m-d H:i:s')
		]);
		if (!$saved) {
			$this->fail(['error' => 'Could not request email change'], 500);
		}

		$url = Mailer::baseUrl() . "/settings/confirm-email?token=$token";
		if (!Mailer::send($email, 'Confirm your new Camagru email',
			"Hi {$user['username']},\r\n\r\nConfirm that this is your new Camagru email by opening the link below (it expires in 30 minutes):\r\n\r\n$url\r\n\r\nIf you didn't request this, you can ignore this email.")) {
			EmailChange::deleteByToken($token);
			$this->fail(['error' => 'Could not send the confirmation email'], 500);
		}

		self::notify($user['email'], 'Camagru email change requested',
			"Hi {$user['username']},\r\n\r\nSomeone asked to change your Camagru email to {$email}. It won't change until the link sent to that address is opened.");

		Helper::json_response([
			'success' => true,
			'message' => "We sent a confirmation link to $email",
			'pending' => $email
		]);
	}

	public function confirmEmail(): void
	{
		$token = trim((string) ($_GET['token'] ?? ''));
		$change = $token ? EmailChange::findByToken($token) : null;

		if (!$change || $change['expires_at'] < (new \DateTime())->format('Y-m-d H:i:s')) {
			$this->confirmPage(false, 'This confirmation link is invalid or has expired.');
		}

		$user = User::find(id: (int) $change['user_id']);
		$existing = User::find(email: $change['new_email']);
		if ($existing && (int) $existing['id'] !== (int) $user['id']) {
			EmailChange::deleteByToken($token);
			$this->confirmPage(false, 'That email is already in use by another account.');
		}

		if (!User::mod($user['id'], 'email', $change['new_email'])) {
			$this->confirmPage(false, 'Could not update your email, please try again.');
		}
		EmailChange::deleteByToken($token);

		self::notify($user['email'], 'Your Camagru email was changed',
			"Hi {$user['username']},\r\n\r\nYour Camagru email was changed to {$change['new_email']}. You won't get notifications at this address anymore.");

		$this->confirmPage(true, "Your email is now {$change['new_email']}.");
	}

	public function updatePassword(): void
	{
		[$user, $data] = $this->authorize();

		$password = trim($data['new_password'] ?? '');
		$confirm = trim($data['confirm'] ?? '');

		try {
			UserValidator::validatePassword($password);
		} catch (\Exception $e) {
			$this->fail(['new_password' => $e->getMessage()]);
		}

		if ($password !== $confirm) {
			$this->fail(['confirm' => 'Passwords does not match']);
		}

		if (password_verify($password, $user['password'])) {
			$this->fail(['new_password' => 'New password must be different from the current one']);
		}

		if (!User::mod($user['id'], 'password', password_hash($password, PASSWORD_DEFAULT))) {
			$this->fail(['error' => 'Could not update password'], 500);
		}

		self::notify($user['email'], 'Your Camagru password was changed',
			"Hi {$user['username']},\r\n\r\nYour Camagru password was just changed.");

		Helper::json_response([
			'success' => true,
			'message' => 'Password updated'
		]);
	}

	public function updateNotifications(): void
	{
		if (!Session::check()) {
			$this->fail(['error' => 'Session lost'], 403);
		}

		$data = json_decode(file_get_contents("php://input"), true);
		if (!is_array($data) || !is_bool($data['notify_comments'] ?? null)) {
			$this->fail(['error' => 'Invalid request']);
		}

		$enabled = $data['notify_comments'];
		if (!User::mod(Session::getId(), 'notify_comments', (int) $enabled)) {
			$this->fail(['error' => 'Could not save'], 500);
		}

		Helper::json_response([
			'success' => true,
			'message' => $enabled ? 'Email notifications on' : 'Email notifications off'
		]);
	}

	public function updateAvatar(): void
	{
		$user = $this->sessionUser();

		$file = $_FILES['avatar'] ?? null;
		if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
			$this->fail(['error' => 'No image received']);
		}
		if ($file['size'] > self::AVATAR_MAX_BYTES) {
			$this->fail(['error' => 'Image is too large']);
		}
		$info = @getimagesize($file['tmp_name']);
		if (!$info || $info[2] !== IMAGETYPE_PNG || $info[0] > 2000 || $info[1] > 2000) {
			$this->fail(['error' => 'Image must be a PNG up to 2000x2000']);
		}
		$source = @imagecreatefrompng($file['tmp_name']);
		if (!$source) {
			$this->fail(['error' => 'Could not read image']);
		}

		$side = min(imagesx($source), imagesy($source));
		$avatar = imagecreatetruecolor(self::AVATAR_SIZE, self::AVATAR_SIZE);
		imagealphablending($avatar, false);
		imagesavealpha($avatar, true);
		imagecopyresampled(
			$avatar, $source, 0, 0,
			intdiv(imagesx($source) - $side, 2), intdiv(imagesy($source) - $side, 2),
			self::AVATAR_SIZE, self::AVATAR_SIZE, $side, $side
		);

		$dir = __DIR__ . '/../../public' . self::AVATAR_URL;
		if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
			$this->fail(['error' => 'Could not prepare upload directory'], 500);
		}
		$url = self::AVATAR_URL . '/' . uniqid('avatar_', true) . '.png';
		if (!imagepng($avatar, __DIR__ . '/../../public' . $url)) {
			$this->fail(['error' => 'Could not save image'], 500);
		}

		if (!User::mod($user['id'], 'avatar', $url)) {
			@unlink(__DIR__ . '/../../public' . $url);
			$this->fail(['error' => 'Could not save image'], 500);
		}
		self::removeAvatarFile($user['avatar']);

		Helper::json_response(['success' => true, 'message' => 'Profile photo updated', 'avatar_url' => $url]);
	}

	public function removeAvatar(): void
	{
		$user = $this->sessionUser();

		if ($user['avatar'] && !User::mod($user['id'], 'avatar', null)) {
			$this->fail(['error' => 'Could not remove photo'], 500);
		}
		self::removeAvatarFile($user['avatar']);

		Helper::json_response(['success' => true, 'message' => 'Profile photo removed', 'avatar_url' => null]);
	}

	public function deleteAccount(): void
	{
		[$user] = $this->authorize();

		$filenames = Image::filenamesForUser((int) $user['id']);

		if (!User::delete((int) $user['id'])) {
			$this->fail(['error' => 'Could not delete account'], 500);
		}

		foreach ($filenames as $filename) {
			$path = __DIR__ . '/../../public' . $filename;
			if (is_file($path)) {
				@unlink($path);
			}
		}
		self::removeAvatarFile($user['avatar']);

		Mailer::send($user['email'], 'Your Camagru account was deleted',
			"Hi {$user['username']},\r\n\r\nYour Camagru account and everything you posted (photos, likes and comments) have been permanently deleted.\r\n\r\nIf you didn't do this, someone else had your password. The deletion can't be undone, but you can create a new account at " . Mailer::baseUrl() . "/register");

		Session::close();
		Helper::json_response([
			'success' => true,
			'message' => 'Account deleted',
			'redirect' => '/'
		]);
	}

	private function sessionUser(): array
	{
		$user = Session::check() ? User::find(id: Session::getId()) : null;
		if (!$user) {
			$this->fail(['error' => 'Session lost'], 403);
		}
		return $user;
	}

	private static function removeAvatarFile(?string $avatar): void
	{
		if ($avatar && str_starts_with($avatar, self::AVATAR_URL . '/')) {
			@unlink(__DIR__ . '/../../public' . $avatar);
		}
	}

	private function authorize(): array
	{
		if (!Session::check()) {
			$this->fail(['error' => 'Session lost'], 403);
		}

		$data = json_decode(file_get_contents("php://input"), true);
		if (!$data) {
			$this->fail(['error' => 'Invalid JSON']);
		}

		$user = User::find(id: Session::getId());
		if (!$user) {
			$this->fail(['error' => 'Session lost'], 403);
		}

		$current = trim($data['current_password'] ?? '');
		if (!$current || !password_verify($current, $user['password'])) {
			$this->fail(['current_password' => 'Current password is incorrect'], 401);
		}

		return [$user, $data];
	}

	private function confirmPage(bool $ok, string $message): void
	{
		$this->view('email-confirm', [
			'title' => 'Email change',
			'ok' => $ok,
			'message' => $message
		]);
		exit;
	}

	private static function pendingEmail(int $userId): ?string
	{
		$change = EmailChange::findByUser($userId);
		if (!$change || $change['expires_at'] < (new \DateTime())->format('Y-m-d H:i:s')) {
			return null;
		}
		return $change['new_email'];
	}

	private static function notify(string $to, string $subject, string $body): void
	{
		Mailer::send($to, $subject, $body . "\r\n\r\nIf this wasn't you, reset your password right away:\r\n" . Mailer::baseUrl() . "/forgot-password");
	}

	private function fail(array $errors, int $status = 400): void
	{
		Helper::json_response(['errors' => $errors], $status);
	}
}
?>
