<?php

$router->post("/admin/products", ["ProductController", "saveItem"]);
$router->post("/admin/products/save-thumbnail", ["ProductController", "saveThumbnail"]);
$router->post("/admin/products/save-additional-images", ["ProductController", "saveAdditionalImages"]);
$router->post("/admin/products/save-category", ["ProductController", "saveCategory"]);

$router->delete("/admin/products", ["ProductController", "deleteItem"]);

$router->get("/admin/products", ["ProductController", "getItem"]);
$router->get("/admin/products/all", ["ProductController", "getItems"]);
