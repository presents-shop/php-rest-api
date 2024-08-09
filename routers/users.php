<?php

$router->post("/users/register", ["UserController", "register"]);
$router->post("/users/login", ["UserController", "login"]);
$router->post("/users/forgot-password", ["UserController", "forgotPassword"]);
$router->post("/users/update-token", ["UserController", "generateNewEmailVerifyToken"]);
$router->post("/users/update", ["UserController", "updateUser"]);

$router->get("/users/email-verify", ["UserController", "emailVerify"]);
$router->get("/users", ["UserController", "getLoggedInUser"]);

$router->delete("/users", ["UserController", "logout"]);

// admin only
$router->get("/admin/users", ["UserController", "getAll"]);