<?php

class IndexController
{
    // GET ROUTES
    public static function getHome()
    {
        Response::ok("Начална страница")->send();
    }

    public static function getAbout()
    {
        Response::ok("Страница за нас")->send();
    }

    public static function getContacts()
    {
        Response::ok("Страница за контакти")->send();
    }
}