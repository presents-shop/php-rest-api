<?php

$router->get("/cities-and-states", ["CityController", "getAllCitiesAndStates"]);
$router->get("/states", ["CityController", "getStates"]);
$router->get("/cities", ["CityController", "getCities"]);

$router->post("/cities/import", ["CityController", "postManyCitiesAndStates"]);
$router->get("/states/generate", ["CityController", "generate"]);
