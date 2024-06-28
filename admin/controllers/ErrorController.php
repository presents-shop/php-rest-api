<?php

class ErrorController
{
    public static function notFound()
    {
        $meta = new MetaTags(
            "Начало",
            "Това е уеб сайт за новини.",
            "новини, новини на български, сайт за новини",
        );

        view("errors/404", ["meta" => $meta->getTags()], 404);
    }

    public static function invalidArgument()
    {
        $meta = new MetaTags(
            "Начало",
            "Това е уеб сайт за новини.",
            "новини, новини на български, сайт за новини",
        );

        view("errors/invalid-argument", ["meta" => $meta->getTags()], 400);
    }
}