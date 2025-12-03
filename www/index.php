<?php	
	$request = $_SERVER['REQUEST_URI'];

	$routes = [
		"/" => "./views/HomeView.php",
		"/settings" => "./views/SettingsView.php",
		"/search" => "./views/SearchView.php",
		"/login" => "./views/Login.php"
	];

	require $routes[$request];
?>