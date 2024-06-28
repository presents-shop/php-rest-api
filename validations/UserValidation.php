<?php

class UserValidation
{
    public static function register($data)
    {
        $errors = [];

        if (empty($data["email"])) {
            $errors["email_empty"] = "Това поле не може да бъде празно";
        }

        if (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
            $errors["email_length"] = "Въведете валиден имейл адрес";
        }

        if (empty($data["phone"])) {
            $errors["phone"] = "Това поле не може да бъде празно";
        }

        if (empty($data["first_name"])) {
            $errors["first_name"] = "Това поле не може да бъде празно";
        }

        if (empty($data["last_name"])) {
            $errors["last_name"] = "Това поле не може да бъде празно";
        }

        if (empty($data["password"])) {
            $errors["password_empty"] = "Това поле не може да бъде празно";
        }

        if (strlen($data["password"]) < 6) {
            $errors["password_length"] = "Паролата трябва да съдържа поне 6 знака";
        }

        if ($data["password"] !== $data["cpassword"]) {
            $errors["passwords_match"] = "Паролите не съвпадат";
        }

        if ($data["code"] !== $_SESSION["code"]) {
            $errors["invalid_code"] = "Невалиден код за сигурност";
        }

        return $errors;
    }

    public static function login($data)
    {
        $errors = [];

        if (empty($data["email"])) {
            $errors["email_empty"] = "Това поле не може да бъде празно";
        }

        if (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
            $errors["email_length"] = "Въведете валиден имейл адрес";
        }

        if (empty($data["password"])) {
            $errors["password_empty"] = "Това поле не може да бъде празно";
        }

        if (strlen($data["password"]) < 6) {
            $errors["password_length"] = "Паролата трябва да съдържа поне 6 знака";
        }

        if ($data["code"] !== $_SESSION["code"]) {
            $errors["invalid_code"] = "Невалиден код за сигурност";
        }

        return $errors;
    }
}