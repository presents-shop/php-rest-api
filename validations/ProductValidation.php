<?php

class ProductValidation
{
    public static function saveItem($data): void
    {
        if (empty($data->title)) {
            Response::badRequest("Заглавието не може да бъде по-малко от 3 знака")->send();
        }
        
        if (empty($data->slug)) {
            Response::badRequest("Въведете URL адрес на продукта")->send();
        }
        
        if (empty($data->original_price)) {
            Response::badRequest("Въведете цена на продукта")->send();
        }
        
        if (!isset($data->quantity)) {
            Response::badRequest("Въведете наличното количество от продукта")->send();
        }
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
