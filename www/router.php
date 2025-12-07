<?php	
	$request = $_SERVER['REQUEST_URI'];

	$routes = [
		"/" => "index.html",
		"/settings" => "./views/SettingsView.php",
		"/search" => "./views/SearchView.php",
		"/login" => "./views/login.html",
		"/register" => "./views/register.html"
	];

	if (array_key_exists($request, $routes))
		require $routes[$request];
	else
		require $routes['/']
?>