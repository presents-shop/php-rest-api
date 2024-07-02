<?php

class AuthGuard
{
    public static function authenticated()
    {
        $token = explode(" ", $_SERVER["HTTP_AUTHORIZATION"])[1] ?? null;

        if (!$token) {
            Response::unauthorized("Нямате достъп до този ресурс")->send();
        }

        try {
            $decoded = JsonWebToken::validateToken($token);

            if (!$decoded) {
                Response::unauthorized("Нямате достъп до този ресурс")->send();
            }

            $user = UserService::findOne($decoded->user_id, "id", "*", true);

            if (!$user) {
                Response::unauthorized("Невалидни потребителски данни")->send();
            }

            if ($decoded->password !== $user["password"]) {
                Response::unauthorized("Паролата е била променена")->send();
            }
        } catch (Exception $ex) {
            Response::badRequest("Нямате достъп до този ресурс")->send();
        }
    }

    public static function guest()
    {
        $token = explode(" ", $_SERVER["HTTP_AUTHORIZATION"])[1] ?? null;

        if ($token) {
            Response::unauthorized("Нямате достъп до този ресурс")->send();
        }
    }
}