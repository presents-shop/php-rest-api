<?php

class CategoryController
{
    // POST ROUTES
    public static function saveItem()
    {
        AuthGuard::authenticated();

        $data = getJSONData();

        CategoryValidation::saveItem($data);

        $id = $data->id ?? null;

        if (!$id) {
            $category = CategoryService::create(getJSONData());
        } else {
            $category = CategoryService::update($id, getJSONData());
        }

        Response::created($category)->send();
    }

    public static function deleteItem()
    {
        AuthGuard::authenticated();

        $id = $_GET["id"];

        $result = CategoryService::delete($id);

        Response::ok($result)->send();
    }

    public static function getItem()
    {
        $id = $_GET["id"];

        $category = CategoryService::findOne($id);

        $category["media"] = self::getItemOptions($category,
        [
            "with_thumbnail" => $_GET["with_thumbnail"] ?? false,
        ]);

        if (!$category) {
            Response::badRequest(["invalid_id" => "Тази категория не съществува"])->send();
        }

        Response::ok($category)->send();
    }

    public static function getItemOptions($category, $params)
    {
        $options = [];

        if (!empty($params["with_thumbnail"]) && $category["thumbnail_id"]) {
            $options["thumbnail"] = MediaService::findOne($category["thumbnail_id"]);
        }

        if (!empty($params["with_additional_images"]) && $category["additional_image_ids"]) {
            $additionalImages = [];
            
            foreach($category["additional_image_ids"] as $id) {
                $additionalImages[] = MediaService::findOne($id);
            }

            $options["additional_images"] = $additionalImages;
        }

        return $options;
    }

    public static function getItems()
    {
        $page = $_GET["page"] ?? 1;
        $limit = $_GET["limit"] ?? null;

        $offset = ($page - 1) * $limit;

        $categories = CategoryService::findAll($offset, $limit);
        $length = CategoryService::getItemsLength();

        foreach($categories as &$category) {
            $category["media"] = self::getItemOptions($category, ["with_thumbnail" => true]);
        }

        Response::ok([
            "items" => $categories,
            "length" => $length,
        ])->send();
    }

    public static function saveThumbnail()
    {
        AuthGuard::authenticated();

        $data = getJSONData();

        CategoryValidation::saveThumbnail($data);

        $category = CategoryService::findOne($data->category_id);

        if (!$category) {
            Response::badRequest($category)->send();
        }

        $image = MediaService::findOne($data->media_id);

        if (!$image) {
            Response::badRequest($image)->send();
        }

        $category = CategoryService::saveThumbnail($data);

        Response::ok($category)->send();
    }

    public static function saveAdditionalImages()
    {
        AuthGuard::authenticated();
        
        $data = getJSONData();

        CategoryValidation::saveAdditionalImages($data);

        $category = CategoryService::findOne($data->category_id);

        if (!$category) {
            Response::badRequest($category)->send();
        }

        foreach($data->media_ids as $id) {
            $image = MediaService::findOne($id);

            if (!$image) {
                Response::badRequest($image)->send();
            }
        }

        $category = CategoryService::saveAdditionalImages($data);

        Response::ok($category)->send();
    }
}