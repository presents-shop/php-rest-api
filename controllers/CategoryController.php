<?php

class CategoryController
{
    // GET METHODS
    public static function getAll()
    {
        $meta = new MetaTags(
            "Всички категории",
        );

        view("categories/index", ["meta" => $meta->getTags()]);
    }
}