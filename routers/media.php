<?php

$router->get("/admin/media", ["MediaController", "getItem"]);
$router->get("/admin/media/all", ["MediaController", "getItems"]);

$router->post("/admin/media/images", ["MediaController", "uploadImage"]);
$router->post("/admin/media", ["MediaController", "saveItem"]);

$router->delete("/admin/media", ["MediaController", "deleteItem"]);
