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
}
?>