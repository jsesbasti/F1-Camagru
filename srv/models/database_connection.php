<?php

$db_server = getenv("DB_SERVER", true) ?: getenv("DB_SERVER") ?: null;
$db_name = getenv("DB_NAME", true) ?: getenv("DB_NAME") ?: null;
$db_username = getenv("DB_USER", true) ?: getenv("DB_USER") ?: null;
$db_password = getenv("DB_PASS",  true) ?: getenv("DB_PASS") ?: null;

echo $db_server;

try {
	$conn = new PDO("mysql:host=$db_server;dbname=$db_name", $db_username, $db_password);
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	echo "connected succesfully";
}
catch (PDOException $e) {
	exit($e->getMessage());
}

?>