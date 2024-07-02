<?php

class CityController
{
    // GET ROUTES
    public static function postManyCitiesAndStates()
    {
        $items = getJSONData();
        CityService::postManyCitiesAndStates($items);

        $statesAndCities = CityService::getAllCitiesAndStates();
        Response::created($statesAndCities)->send();
    }

    public static function getAllCitiesAndStates()
    {
        $statesAndCities = CityService::getAllCitiesAndStates();
        Response::ok($statesAndCities)->send();
    }

    public static function getStates()
    {
        $cities = CityService::getStates();
        Response::ok($cities)->send();
    }

    public static function getCities()
    {
        $stateId = $_GET["state_id"] ?? null;
        $cities = CityService::getCities($stateId);
        Response::ok($cities)->send();
    }
}
