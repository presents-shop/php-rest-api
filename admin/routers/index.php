<?php

$router = new Router();

$uri = $_SERVER["REQUEST_URI"];
$method = $_SERVER["REQUEST_METHOD"];

$router->get("/admin", ["IndexController", "dashboard"]);

require ("users.php");

$router->route($uri, $method);