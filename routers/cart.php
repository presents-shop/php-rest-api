<?php

$router->get("/cart", ["CartController", "getItems"]);

$router->post("/cart", ["CartController", "saveItem"]);
