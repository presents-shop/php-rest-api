<?php

$router->get("/orders", ["OrderController", "getItem"]);
$router->get("/orders/all", ["OrderController", "getItems"]);

$router->post("/orders", ["OrderController", "create"]);
