<?php

class UserController
{
    public static function getRegister()
    {
        self::registerRender();
    }

    public static function getLogin()
    {
        self::loginRender();
    }

    public static function loginRender($errors = []) {
        $_SESSION["code"] = generateSecurityCode();

        $meta = new MetaTags(
            "Влизане в профил",
            "Влезте в профила си от тук.",
        );

        view("users/login", ["meta" => $meta->getTags(), "code" => $_SESSION["code"], "errors" => [...$errors]]);
    }

    public static function registerRender($errors = []) {
        $_SESSION["code"] = generateSecurityCode();

        $meta = new MetaTags(
            "Влизане в профил",
            "Влезте в профила си от тук.",
        );

        view("users/register", ["meta" => $meta->getTags(), "code" => $_SESSION["code"], "errors" => [...$errors]]);
    }

    public static function register() {
        $errors = UserValidation::register($_POST);

        if (count($errors) > 0) {
            self::registerRender($errors);
        }
    }

    public static function login() {
        $errors = UserValidation::login($_POST);

        if (count($errors) > 0) {
            self::loginRender($errors);
        }
    }
}