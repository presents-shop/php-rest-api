<?php

class ProductUtil
{
    public static function getItemOptions($product, $params)
    {
        // Инициализира празен масив, в който ще се съхраняват опции за продукта
        $options = [];

        // Проверява дали е зададен параметърът "with_thumbnail" и дали продуктът има зададено "thumbnail_id"
        if (!empty($params["with_thumbnail"]) && $product["thumbnail_id"]) {
            // Ако е зададено, добавя миниатюрата към опции, като използва MediaService за намиране на изображението по "thumbnail_id"
            $options["thumbnail"] = MediaService::findOne($product["thumbnail_id"]);
        }

        // Проверява дали е зададен параметърът "with_additional_images" и дали продуктът има допълнителни изображения ("additional_image_ids")
        if (!empty($params["with_additional_images"]) && $product["additional_image_ids"]) {
            // Инициализира масив за съхранение на допълнителни изображения
            $additionalImages = [];

            // Обхожда всеки "additional_image_id" и добавя съответното изображение в масива "additionalImages"
            foreach ($product["additional_image_ids"] as $id) {
                $additionalImages[] = MediaService::findOne($id);
            }

            // Добавя масива с допълнителни изображения към опциите на продукта
            $options["additional_images"] = $additionalImages;
        }

        // Връща масива с опциите на продукта
        return $options;
    }
}
