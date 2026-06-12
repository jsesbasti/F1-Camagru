<?php
namespace App\Core;

class Auth {
	public static function check() {
		return isset($_SESSION['user_id']);
	}

	public static function authenticate(int $id) {
		$_SESSION['user_id'] = $id;
	}

}
?>