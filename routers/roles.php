<?php

$router->get("/roles/:id", ["RoleController", "getItem"]);
$router->get("/roles", ["RoleController", "getItems"]);

$router->post("/admin/roles", ["RoleController", "saveItem"]);

$router->delete("/roles/:id", ["RoleController", "deleteItem"]);