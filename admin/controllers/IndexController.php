<?php

class IndexController
{
    public static function dashboard()
    {
        $meta = new MetaTags(
            "Табло",
        );

        view("dashboard", ["meta" => $meta->getTags()]);
    }
}
