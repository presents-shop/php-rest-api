<?php

class UserController
{
    // POST METHODS
    public static function register()
    {
        UserValidation::register(getJSONData());

        $newUser = UserService::register(getJSONData());

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

        try {
            if (!TokenService::verifyToken($tokenString)) {
                Response::badRequest(["invalid_token" => "Невалиден линк за потвърждение на профила."])->send();
            }

            Response::ok([])->send();
        } catch (Exception $ex) {
            Response::badRequest(["invalid_token" => "Невалиден линк за потвърждение на профила."])->send();
        }
    }

    public static function generateNewEmailVerifyToken()
    {
        AuthGuard::authenticated();
        $user = UserService::generateNewEmailVerifyToken();
        Response::ok($user)->send();
    }
}