<?php

class UserController
{
    // POST METHODS
    public static function register() {
        UserValidation::register(getJSONData());

        $newUser = UserService::register(getJSONData());

        Response::created($newUser)->send();
    }

    public static function login() {
        UserValidation::login(getJSONData());

        $token = UserService::login(getJSONData());

        $_SESSION["token"] = $token;

        Response::created(["token" => $token])->send();
    }
}
