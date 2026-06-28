<?php
namespace App\Core;

class Session
{
	public static function check()
	{
		return isset($_SESSION['user_id']);
	}

	public static function authenticate(int $id)
	{
		$_SESSION['user_id'] = $id;
	}

	public static function logout(): void
	{
		session_destroy();
	}

	public static function getId(): int {
		return ($_SESSION['user_id']);
	}
}
?>