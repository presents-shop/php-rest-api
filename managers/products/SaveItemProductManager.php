<?php

class SaveItemProductManager
{
    private static function createOrUpdateProduct($id, $data)
    {
        $product = ProductService::findOne($id);

        if (empty($product)) {
            $product = ProductService::create($data);
            if (!$product) {
                Response::serverError("Грешка при създаване продукта.")->send();
                return false;
            }
        } else {
            $product = ProductService::update($id, $data);
            if (!$product) {
                Response::serverError("Грешка при редактиране на продукта.")->send();
                return false;
            }
        }

        return $product;
    }

    private static function validateCategoryId($categoryId)
    {
        $category = CategoryService::findOne($categoryId);
        if (empty($category)) {
            Response::badRequest("Невалиден идентификатор на категория")->send();
            return false;
        }
        return true;
    }

    private static function validateAdditionalImages($additionalImageIds)
    {
        foreach ($additionalImageIds as $id) {
            if (!self::validateMediaId($id, "допълнителна снимка ($id)")) {
                return false;
            }
        }
        return true;
    }

    private static function validateMediaId($mediaId, $description)
    {
        $media = MediaService::findOne($mediaId);
        if (empty($media)) {
            Response::badRequest("Невалиден идентификатор на $description")->send();
            return false;
        }
        return true;
    }

    private static function validateRelatedIds($data)
    {
        if (isset($data->thumbnail_id) && !self::validateMediaId($data->thumbnail_id, "предна снимка")) {
            return false;
        }

        if (isset($data->additional_image_ids) && !self::validateAdditionalImages($data->additional_image_ids)) {
            return false;
        }

        if (isset($data->category_id) && !self::validateCategoryId($data->category_id)) {
            return false;
        }

        return true;
    }

    private static function validateProductId($id)
    {
        if (empty($id) || !is_string($id)) {
            Response::badRequest("Моля, генерирайте уникален идентификатор на продукта.")->send();
            return false;
        }
        return $id;
    }

    public static function saveItem($data)
    {
        // Валидира данните
        $error = ProductValidation::saveItem($data);
        if (is_string($error)) {
            Response::badRequest($error)->send();
            return;
        }

        // Проверява валидността на ID-то на продукта
        $id = self::validateProductId($data->id);
        if (!$id)
            return;

        // Проверява валидността на свързаните ID-та (thumbnail, additional images, category)
        if (!self::validateRelatedIds($data))
            return;

        // Търси или създава нов продукт
        $product = self::createOrUpdateProduct($id, $data);
        if (!$product)
            return;

        // Добавя медийни данни към продукта
        $product["media"] = ProductUtil::getItemOptions($product, [
            "with_thumbnail" => true,
            "with_additional_images" => true,
        ]);

        // Връща успешен отговор със статус 200 (OK) и данни за продукта
        Response::ok($product)->send();
    }
}