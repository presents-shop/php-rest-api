<?php

class ProductController
{
    // GET METHODS
    public static function getAll()
    {
        $meta = new MetaTags(
            "Всички продукти",
        );

        view("products/index", ["meta" => $meta->getTags()]);
    }
}