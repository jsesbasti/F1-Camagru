<?php

$uid = getenv("OAUTH_UID", true) ?: getenv("OAUTH_UID");
$secret = getenv("OAUTH_SECRET", true) ?: getenv("OAUTH_SECRET");

include "./models/response.php";

$database_error = new Response(["code" => 500, "msg" => "database connection error"]);

function postLogin() {
	include './models/database_connection.php';
	global $database_error;
	global $conn;

	// echo global $database_error::jsonSerialize();
	if ($conn == null)
		echo json_encode($database_error);
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