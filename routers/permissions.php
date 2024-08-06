<?php

$router->get("/permissions/:id", ["PermissionController", "getItem"]);
$router->get("/permissions", ["PermissionController", "getItems"]);

$router->post("/permissions", ["PermissionController", "saveItem"]);

$router->delete("/permissions", ["PermissionController", "deleteItem"]);
