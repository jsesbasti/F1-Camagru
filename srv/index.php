<?php
	$request = $_SERVER['REQUEST_URI'];
	$routes = [
		"/" => "./views/HomeView.php",
		"/settings" => "SettingsView.php",
		"/search" => "SearchView.php"
	];

	require $routes[$request];
?>