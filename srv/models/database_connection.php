<?php

$db_server = getenv("DB_SERVER", true) ?: getenv("DB_SERVER") ?: null;
$db_name = getenv("DB_NAME", true) ?: getenv("DB_NAME") ?: null;
$db_username = getenv("DB_USER", true) ?: getenv("DB_USER") ?: null;
$db_password = getenv("DB_PASS",  true) ?: getenv("DB_PASS") ?: null;

try {
	$conn = new mysqli($server_name, $db_username, $db_password);

	if ($conn->connect_error) {
		$conn = null;
		throw new Exception("could not login");
	}
}
catch (Exception $e) {
	exit($e->getMessage());
}

?>