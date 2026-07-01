<?php
namespace App\Validators;

use Exception;

define('NOT_EMAIL', 'Email is required');
define('EMAIL_FORMAT_ERROR', 'Invalid email format');
define('NOT_USERNAME', 'Username is required');
define('USERNAME_FORMAT_ERROR', 'Username must be 4-20 chars and only contain letters, numbers and underscores');
define('NOT_PASSWORD', 'Password is required');
define('P_LEN', 'Password must contain at least 12 characters');
define('P_LOW_CASE', 'Password must contain at least one LOWER letter');
define('P_UP_CASE', 'Password must contain at least one UPPER letter');
define('P_NUM_CASE', 'Password must contain at least one number');
define('P_SPEC_CHAR', 'Password must contain at least one special character');


class UserValidator {

	public static function validateRegister(array $data): array {

		$errors = [];

		$email = trim($data['email'] ?? '');
		$username = trim($data['username'] ?? '');
		$password = trim($data['password'] ?? '');
		
		$email_err = '';

		try {
			self::validateEmail($email);
		} catch (Exception $e) {
			$errors['email'] = $e->getMessage();
		}

		try {
			self::validateUsername($username);
		} catch (Exception $e) {
			$errors['username'] = $e->getMessage();
		}

		try {
			self::validatePassword($password);
		} catch (Exception $e) {
			$errors['password'] = $e->getMessage();
		}

		return $errors;
	}

	public static function validateEmail(string $email) {
		if (!$email)
			throw new Exception(NOT_EMAIL);
		if (!filter_var($email, FILTER_VALIDATE_EMAIL))
			throw new Exception(EMAIL_FORMAT_ERROR);
		return 0;
	}

	public static function validateUsername(string $username) {
		if (!$username)
			throw new Exception(NOT_USERNAME);
		if (!preg_match('/^[a-zA-Z0-9_]{4,20}$/', $username))
			throw new Exception(USERNAME_FORMAT_ERROR);
		return 0;
	}

	public static function validatePassword(string $password) {
		if (!$password)
			throw new Exception(NOT_PASSWORD);
		if (strlen($password) < 12) {
        	throw new \Exception(P_LEN);
		}
    	if (!preg_match('/[a-z]/', $password)) {
    	    throw new \Exception(P_LOW_CASE);
    	}
    	if (!preg_match('/[A-Z]/', $password)) {
    	    throw new \Exception(P_UP_CASE);
    	}
    	if (!preg_match('/\d/', $password)) {
    	    throw new \Exception(P_NUM_CASE);
    	}
    	if (!preg_match('/[^a-zA-Z\d]/', $password)) {
    	    throw new \Exception(P_SPEC_CHAR);
    	}
		return 0;
	}
}

?>