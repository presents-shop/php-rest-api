<?php

require "vendor/autoload.php";

use Jchook\Uuid;

class CityService
{
    public static function postManyCitiesAndStates($items = [])
    {
        try {
            global $database;

            $database->beginTransaction();

            foreach ($items as $item) {
                $stateId = Uuid::v4();

                if (isset($item->state) && $item->state) {
                    $data = [
                        "id" => $stateId,
                        "name" => $item->state,
                    ];
                    $database->insert("states", $data);
                }

                if (isset($item->cities) && is_array($item->cities)) {
                    foreach ($item->cities as $city) {
                        $data = [
                            "id" => Uuid::v4(),
                            "name" => $city,
                            "state_id" => $stateId,
                        ];
                        $database->insert("cities", $data);
                    }
                }
            }

            $database->commit();
        } catch (Exception $ex) {
            $database->rollBack();
            Response::serverError($ex->getMessage())->send();
        }
    }

    public static function getAllCitiesAndStates()
    {
        global $database;

        try {
            $states = $database->getAll("SELECT * FROM states");

            foreach ($states as &$state) {
                $params = [":state_id" => $state["id"]];
                $state["cities"] = $database->getAll("SELECT * FROM cities WHERE state_id = :state_id", $params);
            }

            return $states;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function getStates($columns = "*")
    {
        global $database;

        try {
            $states = $database->getAll("SELECT $columns FROM states");
            return $states;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function getCities($stateId = null, $columns = "*")
    {
        global $database;
        $sql = "SELECT $columns FROM cities";
        $params = [];

        if ($stateId) {
            $sql .= " WHERE state_id = :state_id";
            $params[":state_id"] = $stateId;
        }

        try {
            $cities = $database->getAll($sql, $params);
            return $cities;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function generateToExcel($json = [], $filename = "cities.xlsx")
    {
        try {
            $folder = "temp/";

            if (!file_exists($folder)) {
                mkdir($folder, 0777, false);
            }

            ExcelGenerator::generateFromJson($json, $folder.$filename);
        } catch(Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
}