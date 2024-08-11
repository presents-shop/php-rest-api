<?php

class ProductPopulator
{
    public static function populateDependencies($product, $params)
    {
        // Добавя миниатюрата към продукта, ако е необходимо
        $product["media"]["thumbnail"] = self::populateThumbnail($product, $params);

        // Добавя допълнителни изображения към продукта, ако е необходимо
        $product["media"]["additional_images"] = self::populateAdditionalImages($product, $params);

        // Добавя категорията към продукта, ако е необходимо
        $product["category"] = self::populateCategory($product, $params);

        // Връща продукта с добавените зависимости
        return $product;
    }

    public static function populateThumbnail($product, $params)
    {
        if (!empty($params["with_thumbnail"]) && $product["thumbnail_id"]) {
            return MediaService::findOne($product["thumbnail_id"]);
        }
        return null;
    }

    public static function populateAdditionalImages($product, $params)
    {
        $additionalImages = [];

        if (!empty($params["with_additional_images"]) && $product["additional_image_ids"]) {
            foreach ($product["additional_image_ids"] as $id) {
                $additionalImages[] = MediaService::findOne($id);
            }
        }

        return $additionalImages;
    }

    public static function populateCategory($product, $params)
    {
        if (!empty($params["with_category"]) && $product["category_id"]) {
            return CategoryService::findOne($product["category_id"]);
        }
        return null;
    }
}
