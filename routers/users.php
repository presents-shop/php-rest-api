<?php

$router->get("/users/register", ["UserController", "getRegister"]);
$router->get("/users/login", ["UserController", "getLogin"]);

$router->post("/users/register", ["UserController", "register"]);
$router->post("/users/login", ["UserController", "login"]);