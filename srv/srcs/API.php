<?php
	$router = new Router();

	$router->post("/login", postLogin());
	$router->get("/callback", callback());

	require $routes[$request];
?>