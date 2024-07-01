<?php

class ProductValidation
{
    public static function saveItem($data)
    {
        $errors = [];

        if (empty($data->title)) {
            $errors["title"] = "Заглавието не може да бъде по-малко от 3 знака";
        }

        if (empty($data->slug)) {
            $errors["slug"] = "Въведете URL адрес на продукта";
        }

        if (empty($data->original_price)) {
            $errors["original_price"] = "Въведете цена на продукта";
        }

        if (!isset($data->quantity)) {
            $errors["quantity"] = "Въведете наличното количество от продукта";
        }

        if (count($errors) > 0) {
            Response::badRequest($errors)->send();
        }
    }

    public static function saveThumbnail($data)
    {
        $errors = [];
        
        if (empty($data->product_id)) {
            $errors["invalid_id"] = "Id на продукта не може да бъде празно";
        }
        
        if (empty($data->media_id)) {
            $errors["media_id"] = "Id на снимката не може да бъде празно";
        }

        if (count($errors) > 0) {
            Response::badRequest($errors)->send();
        }
    }

    public static function saveAdditionalImages($data)
    {
        $errors = [];
        
        if (empty($data->product_id)) {
            $errors["invalid_id"] = "Id на продукта не може да бъде празно";
        }
        
        if (empty($data->media_ids)) {
            $errors["media_id"] = "Ids на снимките не могат да бъдат празни";
        }

        if (count($errors) > 0) {
            Response::badRequest($errors)->send();
        }
    }

    public static function saveCategory($data)
    {
        $errors = [];
        
        if (empty($data->product_id)) {
            $errors["invalid_id"] = "Id на продукта не може да бъде празно";
        }
        
        if (empty($data->category_id)) {
            $errors["category_id"] = "Ids на снимките не могат да бъдат празни";
        }

        if (count($errors) > 0) {
            Response::badRequest($errors)->send();
        }
    }
}
