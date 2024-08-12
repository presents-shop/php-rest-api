<?php

$router->get("/orders", ["OrderController", "getItem"]);

$router->post("/orders", ["OrderController", "create"]);
