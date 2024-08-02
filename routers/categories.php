<?php

$router->post("/admin/categories", ["CategoryController", "saveItem"]);
$router->post("/admin/categories/save-thumbnail", ["CategoryController", "saveThumbnail"]);
$router->post("/admin/categories/save-additional-images", ["CategoryController", "saveAdditionalImages"]);

$router->delete("/admin/categories", ["CategoryController", "deleteItem"]);

$router->get("/categories", ["CategoryController", "getItem"]);
$router->get("/categories/all", ["CategoryController", "getItems"]);
