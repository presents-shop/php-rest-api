<?php

$router = new Router();

$uri = $_SERVER["REQUEST_URI"];
$method = $_SERVER["REQUEST_METHOD"];

$router->get("/", ["IndexController", "getHome"]);
$router->get("/about", ["IndexController", "getAbout"]);
$router->get("/contacts", ["IndexController", "getContacts"]);

require "users.php";

$router->route($uri, $method);