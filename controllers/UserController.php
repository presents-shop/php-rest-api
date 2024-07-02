<?php

class UserController
{
    // POST METHODS
    public static function register()
    {
        $data = getJSONData();

        UserValidation::register($data);

        $newUser = UserService::register($data);

        UserService::sendVerifyEmail($newUser["email"], $newUser["token"]);

        Response::created($newUser)->send();
    }

    public static function login()
    {
        UserValidation::login(getJSONData());

        $token = UserService::login(getJSONData());

        $_SESSION["token"] = $token;

        Response::created(["token" => $token])->send();
    }

    public static function emailVerify()
    {
        $tokenString = $_GET["token"] ?? null;
        UserService::emailVerify($tokenString);
        Response::ok([])->send();
    }

    public static function generateNewEmailVerifyToken()
    {
        AuthGuard::authenticated();
        $user = UserService::generateNewEmailVerifyToken();
        Response::ok($user)->send();
    }

    public static function forgotPassword()
    {
        $data = getJSONData();

        if (empty($data->email)) {
            Response::badRequest("invalid_email", "Невалиден имейл адрес")->send();
        }

        $result = UserService::forgotPassword($data->email);

        if (!$result) {
            Response::badRequest("invalid_email", "Невалиден имейл адрес")->send();
        }

        Response::ok([])->send();
    }
}