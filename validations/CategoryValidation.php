<?php

class CategoryValidation
{
    public static function saveItem($data)
    {
        if (empty($data->title)) {
            return "Заглавието не може да бъде по-малко от 3 знака";
        }
        
        if (empty($data->slug)) {
            return "Въведете URL адрес на категорията";
        }
        
        return true;
    }

    public static function saveThumbnail($data)
    {
        $errors = [];
        
        if (empty($data->category_id)) {
            $errors["invalid_id"] = "Id на категорията не може да бъде празно";
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
        
        if (empty($data->category_id)) {
            $errors["invalid_id"] = "Id на категорията не може да бъде празно";
        }
        
        if (empty($data->media_ids)) {
            $errors["media_id"] = "Ids на снимките не могат да бъдат празни";
        }

        if (count($errors) > 0) {
            Response::badRequest($errors)->send();
        }
    }
}
