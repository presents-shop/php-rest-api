<?php

$router->post("/users/register", ["UserController", "register"]);
$router->post("/users/login", ["UserController", "login"]);
$router->post("/users/forgot-password", ["UserController", "forgotPassword"]);
$router->post("/users/update-token", ["UserController", "generateNewEmailVerifyToken"]);

$router->get("/users/email-verify", ["UserController", "emailVerify"]);

// admin only
$router->get("/admin/users", ["UserController", "getAll"]);
