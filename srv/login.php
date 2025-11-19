<?php

function login($data) {
	include 'database_connection.php';

	if ($conn == null)
		return (json_encode(array("code" => 500, "msg" => "database connection error")));
}

login(null);

?>