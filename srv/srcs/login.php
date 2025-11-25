<?php

$uid = getenv("OAUTH_UID", true) ?: getenv("OAUTH_UID");
$secret = getenv("OAUTH_SECRET", true) ?: getenv("OAUTH_SECRET");


$database_error = new Response(["code" => 500, "msg" => "database connection error"]);

function postLogin() {
	include 'database_connection.php';
	global $database_error;

	// echo global $database_error::jsonSerialize();
	if ($conn == null)
		return (json_encode($database_error));
}

function getLogin() {
	
}

// login(null);

function callback() {
	include 'database_connection.php';
	global $database_error;
	
	if ($conn === null)
		return (json_encode($database_error));
}

?>