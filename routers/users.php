<?php

$router->post("/users/register", ["UserController", "register"]);
$router->post("/users/login", ["UserController", "login"]);
$router->post("/users/forgot-password", ["UserController", "forgotPassword"]);

// admin only
$router->get("/admin/users", ["UserController", "getAll"]);
