<?php

$router->get("/admin/categories", ["CategoryController", "getAll"]);
$router->get("/admin/categories/create", ["CategoryController", "getCreate"]);

$router->post("/admin/categories/create", ["CategoryController", "create"]);
