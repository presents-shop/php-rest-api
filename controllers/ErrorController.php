<?php

class ErrorController
{
    public static function notFound()
    {
        Response::notFound("Тази страница не е намерена.")->send();
    }
    
    public static function invalidArgument()
    {
        Response::badRequest("Невалиден аргумент.")->send();
    }
}