<?php

class IndexController
{
    // GET ROUTES
    public static function getHome()
    {
        $meta = new MetaTags(
            "Начало",
            "Това е уеб сайт за новини.",
            "новини, новини на български, сайт за новини",
        );

        view("index/home", ["meta" => $meta->getTags()]);
    }

    public static function getAbout()
    {
        $meta = new MetaTags(
            "Начало",
            "Това е уеб сайт за новини.",
            "новини, новини на български, сайт за новини",
        );

        view("index/about", ["meta" => $meta->getTags()]);
    }

    public static function getContacts()
    {
        $meta = new MetaTags(
            "Начало",
            "Това е уеб сайт за новини.",
            "новини, новини на български, сайт за новини",
        );

        view("index/contacts", ["meta" => $meta->getTags()]);
    }
}
