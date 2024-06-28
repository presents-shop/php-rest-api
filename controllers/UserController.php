<?php

class UserController
{
    // GET METHODS
    
    public static function getRegister()
    {
        self::registerRender();
    }

    public static function getLogin()
    {
        self::loginRender();
    }

    public static function getForgotPassword()
    {
        self::forgotPasswordRender();
    }

    // RENDER METHODS
    public static function loginRender($errors = []) {
        $_SESSION["code"] = generateSecurityCode();

        $meta = new MetaTags(
            "Влизане в профил",
            "Влезте в профила си от тук.",
        );

        view("users/login", [
            "meta" => $meta->getTags(),
            "code" => $_SESSION["code"],
            "errors" => [...$errors],
            "input" => $_POST,
        ]);
    }

    public static function registerRender($errors = []) {
        $_SESSION["code"] = generateSecurityCode();

        $meta = new MetaTags(
            "Влизане в профил",
            "Влезте в профила си от тук.",
        );

        view("users/register", [
            "meta" => $meta->getTags(),
            "code" => $_SESSION["code"],
            "errors" => [...$errors],
            "input" => $_POST,
        ]);
    }

    // POST METHODS
    public static function forgotPasswordRender($errors = []) {
        $_SESSION["code"] = generateSecurityCode();

        $meta = new MetaTags(
            "Въведете вашия имейл адрес",
        );

        view("users/forgot-password", ["meta" => $meta->getTags(), "code" => $_SESSION["code"], "errors" => [...$errors]]);
    }

    public static function register() {
        $errors = UserValidation::register($_POST);

        if (count($errors) > 0) {
            self::registerRender($errors);
        }

        $userService = new UserService();
        $newUser = $userService->register($_POST);

        if (!$newUser) {
            self::registerRender($userService->getErrors());
        }

        redirect("/users/login");
    }

    public static function login() {
        $errors = UserValidation::login($_POST);

        if (count($errors) > 0) {
            self::loginRender($errors);
        }

        
        $userService = new UserService();
        $token = $userService->login($_POST);
        
        if (!$token) {
            self::loginRender($userService->getErrors());
        }

        $_SESSION["token"] = $token;

        redirect("/");
    }

    public static function forgotPassword() {
        $errors = UserValidation::login($_POST);

        if (count($errors) > 0) {
            self::loginRender($errors);
        }
    }
}