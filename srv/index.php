<?php
	$router = new Router();

	$router->post("/login", postLogin());
	$router->get("/callback", callback());
	

	$router->resolve();
?>