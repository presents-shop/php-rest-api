<?php

class CategoryValidation
{
    public static function create($data)
    {
        $errors = [];

        if (empty($data["title"])) {
            $errors["title"] = "Това поле не може да бъде празно";
        }

        if (empty($data["slug"])) {
            $errors["slug"] = "Това поле не може да бъде празно";
        }

        if ($data["code"] !== $_SESSION["code"]) {
            $errors["invalid_code"] = "Невалиден код за сигурност";
        }

        return $errors;
    }
}
