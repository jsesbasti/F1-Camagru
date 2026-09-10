<?php
namespace App\Controllers;

use App\Models\User;
use App\Models\Image;
use App\Core\Controller;
use App\Core\Session;
use App\Validators\UserValidator;
use App\Core\Helper;

class UserController extends Controller
{
	public function profile()
	{
		$this->requireAuth();
		// $currentUser is always the signed-in visitor (the header/footer nav
		// depend on it); $profileUser is whichever profile is being shown —
		// themselves by default, or another user via ?username=.
		$currentUser = User::find(id: Session::getId());

		$requestedUsername = trim((string) ($_GET['username'] ?? ''));
		$isOwnProfile = $requestedUsername === '' || strcasecmp($requestedUsername, $currentUser['username']) === 0;
		$profileUser = $isOwnProfile ? $currentUser : User::find(username: $requestedUsername);

		if (!$profileUser) {
			$this->view('profile', [
				'title' => 'Profile not found',
				'currentUser' => $currentUser,
				'userNotFound' => true,
				'scripts' => [
					'auth'
				]
			]);
			return;
		}

		$photos = Image::forUser((int) $profileUser['id']);
		$likesReceived = array_sum(array_column($photos, 'likes_count'));

		$this->view('profile', [
			'title' => $isOwnProfile ? 'Profile' : $profileUser['username'] . ' · Profile',
			'currentUser' => $currentUser,
			'profileUser' => $profileUser,
			'isOwnProfile' => $isOwnProfile,
			'photos' => $photos,
			'photosCount' => count($photos),
			'likesReceived' => $likesReceived,
			'scripts' => [
				'auth'
			]
		]);
	}

	public function updateEmail() {
		if (Session::check()) {
			$id = Session::getId();
			$user = User::find(id: $id);
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
		if ($old_email != $new_email) {
			try {
				UserValidator::validateEmail($new_email);
			} catch (\Exception $e) {
				$errors['new_email'] = $e->getMessage();
			}
		}
		else {
			Helper::json_response([
				'errors' => [
					'Emails can not be equal'
				]
			], 400);
		}

		if (User::mod($id, 'email', $new_email)) {
			Helper::json_response(['success' => true]);
		} 
		else {
			Helper::json_response([
				'errors' => [
					'email' => 'Email in use'
				]
			], 400);
		}
	}

	public function deleteUser() {
		if (Session::check()) {
			$id = Session::getId();
		}
		else {
			Helper::json_response([
				'errors' => [
					'error' => 'Session lost'
				]
			], 403);
		}
		$data = json_decode(file_get_contents("php://input"), true);
		$confirmation = trim($data['confirm'] ?? '');
		if (!$confirmation) {
			Helper::json_response([
				'errors' => [
					'error' => 'confirmation needed'
				]
			], 401);
		}
		if (User::delete($id)) {
			Session::close();
			Helper::json_response([
				'success' => true,
				'redirect' => '/'
			], 200);
		}
	}
}
?>