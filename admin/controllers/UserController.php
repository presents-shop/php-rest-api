<?php

class UserController
{
    public static function index()
    {
        $user = new UserService();

        $users = $user->findAll();

        $meta = new MetaTags(
            "Потребители" . " | " . "Табло",
        );

        view("users/index", ["users" => $users, "meta" => $meta->getTags()]);
    }
}