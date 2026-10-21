<?php
namespace App\Controllers;

use App\Models\User;
use App\Models\Image;
use App\Models\Follow;
use App\Core\Controller;
use App\Core\Session;
use App\Validators\UserValidator;
use App\Core\Helper;
use App\Core\Mailer;

class UserController extends Controller
{
	public function search(): void
	{
		$this->requireAuth();
		$query = trim((string) ($_GET['q'] ?? ''));

		$this->view('search', [
			'title' => 'Search',
			'currentUser' => User::find(id: Session::getId()),
			'query' => $query,
			'results' => $query !== '' ? User::search(mb_substr($query, 0, 50)) : [],
			'scripts' => [
				'search'
			]
		]);
	}

	public function searchApi(): void
	{
		if (!Session::check()) {
			Helper::json_response(['errors' => ['error' => 'You need to log in'], 'redirect' => '/login'], 401);
		}
		$query = trim((string) ($_GET['q'] ?? ''));
		$results = $query !== '' ? User::search(mb_substr($query, 0, 50)) : [];

		Helper::json_response(['results' => array_map(static fn (array $u) => [
			'username' => $u['username'],
			'avatar_url' => $u['avatar'],
		], $results)]);
	}

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
			'follows' => Follow::counts((int) $profileUser['id']),
			'isFollowing' => !$isOwnProfile && Follow::isFollowing((int) $currentUser['id'], (int) $profileUser['id']),
			'scripts' => [
				'profile'
			]
		]);
	}

	public function follow(): void
	{
		if (!Session::check()) {
			Helper::json_response(['errors' => ['error' => 'You need to log in'], 'redirect' => '/login'], 401);
		}

		$data = json_decode(file_get_contents("php://input"), true) ?: [];
		$followedId = (int) ($data['user_id'] ?? 0);
		$followerId = Session::getId();

		if ($followedId === $followerId) {
			Helper::json_response(['errors' => ['error' => 'You cannot follow yourself']], 400);
		}
		if (!$followedId || !User::find(id: $followedId)) {
			Helper::json_response(['errors' => ['error' => 'User not found']], 404);
		}

		$following = Follow::toggle($followerId, $followedId);

		// Answer first, then email: SMTP is slow.
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode(['success' => true, 'following' => $following] + Follow::counts($followedId));
		if (function_exists('fastcgi_finish_request')) {
			fastcgi_finish_request();
		}

		$followed = User::find(id: $followedId);
		if ($following && $followed['notify_comments'] && Follow::markNotified($followerId, $followedId)) {
			$follower = User::find(id: $followerId);
			Mailer::send($followed['email'], "{$follower['username']} started following you",
				"Hi {$followed['username']},\r\n\r\n{$follower['username']} started following you on Camagru.\r\n\r\nSee their profile: " . Mailer::baseUrl() . '/profile?username=' . urlencode($follower['username'])
				. "\r\n\r\nYou can turn these emails off in Settings: " . Mailer::baseUrl() . "/settings");
		}
		exit;
	}
}
?>