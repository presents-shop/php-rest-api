<?php

class UserValidation
{
    public static function register($data)
    {
        $errors = [];

        if (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
            $errors["invalid_email"] = "Въведете валиден имейл адрес";
        }
        
        if (empty($data->phone) || !self::validatePhoneNumber($data->phone)) {
            $errors["invalid_phone"] = "Въведете валиден телефонен номер";
        }
        
        if (empty($data->first_name)) {
            $errors["first_name"] = "Въведете валиден телефонен номер";
        }
        
        if (empty($data->last_name)) {
            $errors["last_name"] = "Въведете валидна фамилия";
        }
        
        if (empty($data->password) || strlen($data->password) < 6) {
            $errors["password"] = "Паролата трябва да съдържа поне 6 знака";
        }
        
        if (empty($data->cpassword) || $data->password !== $data->cpassword) {
            $errors["match_passwords"] = "Паролите не съвпадат";
        }

        if (count($errors) > 0) {
            Response::badRequest($errors)->send();
        }
    }

    public static function login($data)
    {
        $errors = [];

        if (empty($data->email) || !filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
            $errors["email_length"] = "Въведете валиден имейл адрес";
        }
        
        if (empty($data->password) ||strlen($data->password) < 6) {
            $errors["password_length"] = "Паролата трябва да съдържа поне 6 знака";
        }

        return $errors;
    }

    public static function validatePhoneNumber(string $phoneNumber): Bool {
        $phoneNumber = preg_replace('/\D/', '', $phoneNumber);
        
        if (strlen($phoneNumber) < 7) {
            return false;
        }
        
        $pattern = '/^(0|\+359|359)?(87|88|89)[2-9]\d{6}$/';

        if (preg_match($pattern, $phoneNumber)) {
            return true;
        } else {
            return false;
        }
    }
}
