<?php
namespace App\Validators;

class UserValidator {

	public static function validateRegister(array $data): array {

		$errors = [];

		$email = trim($data['email'] ?? '');
		$username = trim($data['username'] ?? '');
		$password = trim($data['password'] ?? '');
	
		if (!$email) {
			$errors['email'] = 'Email is required';

		} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$errors['email'] = 'Invalid email';
		}

		if (!$username) {

			$errors['username'] = 'Username is required';

		} elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {

			$errors['username'] =
				'Username must be 3-20 chars and only contain letters, numbers and underscores';
		}

		if (!$password) {

			$errors['password'] = 'Password is required';

		} elseif (strlen($password) < 12) {

			$errors['password'] =
				'Password must contain at least 12 characters';
		}

		return $errors;
	}
}

?>