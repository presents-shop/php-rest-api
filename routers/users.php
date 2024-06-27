<?php

// all users
$router->get("/users/register", ["UserController", "getRegister"]);
$router->get("/users/login", ["UserController", "getLogin"]);
$router->get("/users/forgot-password", ["UserController", "getForgotPassword"]);

$router->post("/users/register", ["UserController", "register"]);
$router->post("/users/login", ["UserController", "login"]);
$router->post("/users/forgot-password", ["UserController", "forgotPassword"]);

// admin only
$router->get("/admin/users", ["UserController", "getAll"]);
