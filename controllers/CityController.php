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

    public static function generate()
    {
        $type = $_GET["type"] ?? null; // example: excel
        $filename = $_GET["filename"] ?? "cities.xlsx";
        $stateId = $_GET["state_id"] ?? null; // generate by state id or all cities
        $whatGen = $_GET["what_gen"] ?? "states"; // generate states or cities

        if (!$type) {
            Response::badRequest(["invalid_type" => "Невалиден тип на файла"])->send();
        }
        
        if ($type === "excel" && $whatGen === "states") {
            $states = CityService::getStates("name AS 'Областен град'");
            CityService::generateToExcel(json_encode($states), $filename);
            ExcelGenerator::downloadFile("temp/".$filename);
        }

        else if ($type === "excel" && $whatGen === "cities") {
            $cities = CityService::getCities($stateId, "name AS 'Име на град'");
            CityService::generateToExcel(json_encode($cities), $filename);
            ExcelGenerator::downloadFile("temp/".$filename);
        }

        Response::created()->send();
    }
}
