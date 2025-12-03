<?php

$uid = getenv("OAUTH_UID", true) ?: getenv("OAUTH_UID");
$secret = getenv("OAUTH_SECRET", true) ?: getenv("OAUTH_SECRET");

include "./models/response.php";
include './models/database_connection.php';

class LoginController {
	function loginUser() {
		$result = ['user' => null, 'password' => null];
		echo json_encode($_POST);
		parse_str($_POST, $result);
		// echo json_encode($result);
		if ($result['user'] == null || $result['password'] == null) #se deberia encargar frontend de que no llegue null
			json_response(['msg' => 'invalid credentials'], 401);
		$user = $result['user'];
		$password = convert_uuencode($result['password']);
		echo $password;
		$password = convert_uudecode($password);
		echo $password;
		$conn = makeQuery("SELECT * FROM users WHERE 'username==$user'");

		json_response($_SERVER, 200);
	}

	function registerUser() {
		password_hash($result['password'], PASSWORD_DEFAULT);
	}
}



// login(null);

// function callback() {
// 	include './models/database_connection.php';
// 	global $database_error;
// 	global $conn;
	
// 	if ($conn === null)
// 		echo "hola";
// 	return (json_encode($database_error));
// }

?>