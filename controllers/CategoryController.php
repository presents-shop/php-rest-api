<?php

class CategoryController
{
    // POST ROUTES
    public static function saveItem()
    {
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
        $id = $_GET["id"];

        $result = CategoryService::delete($id);

        Response::ok($result)->send();
    }

    public static function getItem()
    {
        $id = $_GET["id"];

        $category = CategoryService::findOne($id);

        if (!$category) {
            Response::badRequest(["invalid_id" => "Тази категория не съществува"])->send();
        }

        Response::ok($category)->send();
    }

    public static function getItems()
    {
        $page = $_GET["page"] ?? 1;
        $limit = $_GET["limit"] ?? null;

        $offset = ($page - 1) * $limit;

        $categories = CategoryService::findAll($offset, $limit);

        Response::ok($categories)->send();
    }

    public static function saveThumbnail()
    {
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