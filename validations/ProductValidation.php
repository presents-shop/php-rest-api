<?php

class ProductValidation
{
    public static function saveItem($data)
    {
        if (empty($data->name) || strlen($data->name) < 3) {
            return "Заглавието не може да бъде по-малко от 3 знака.";
        }
        
        if (empty($data->slug)) {
            return "Въведете URL адрес на продукта.";
        }

        if (empty($data->sku)) {
            return "Въведете уникален код на продукта.";
        }

        if (empty($data->weight) || !is_numeric($data->weight) || $data->weight <= 0) {
            return "Въведете теглото на продукта.";
        }

        if (empty($data->dimensions) || !is_string($data->dimensions)) {
            return "Въведете размерите на продукта (дължина, ширина, височина).";
        }

        return true;
    }
    
    public static function saveThumbnail($data): void
    {
        if (empty($data->product_id)) {
            Response::badRequest("Id на продукта не може да бъде празно")->send();
        }
        
        if (empty($data->media_id)) {
            Response::badRequest("Id на снимката не може да бъде празно")->send();
        }
    }
    
    public static function saveAdditionalImages($data)
    {
        if (empty($data->product_id)) {
            Response::badRequest("Id на продукта не може да бъде празно")->send();
        }
        
        if (empty($data->media_ids)) {
            Response::badRequest("Ids на снимките не могат да бъдат празни")->send();
        }
    }
    
    public static function saveCategory($data)
    {
        $errors = [];
        
        if (empty($data->product_id)) {
            Response::badRequest("Id на продукта не може да бъде празно")->send();
        }
        
        if (empty($data->category_id)) {
            Response::badRequest("Id на категорията не може да бъде празно")->send();
        }
    }
}
