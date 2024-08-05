<?php

class MediaController
{
    // POST ROUTES
    public static function uploadImage()
    {
        $mediaService = new MediaService();
        $id = $mediaService->uploadImage("image", "uploads");

        Response::created(["id" => $id])->send();
    }

    public static function saveItem()
    {
        $data = getJSONData();

        $id = $data->id ?? null;

        if (!$id) {
            Response::badRequest(["invalid_id" => "Този файл не съществува"])->send();
        } else {
            $media = MediaService::update($id, getJSONData());
        }

        Response::created($media)->send();
    }

    public static function getItems()
    {
        $page = $_GET["page"] ?? 1;
        $limit = $_GET["limit"] ?? null;
        $sort = $_GET["sort"] ?? null;

        $offset = ($page - 1) * $limit;

        $mediaItems = MediaService::findAll($offset, $limit, $sort);
        $length = MediaService::getItemsLength();
        
        Response::ok([
            "items" => $mediaItems,
            "length" => $length,
            "params" => [
                "page" => intval($page),
                "limit" => intval($limit),
                "sort" => $sort,
            ]
        ])->send();
    }

    public static function getItem()
    {
        $id = $_GET["id"] ?? null;

        $item = MediaService::findOne($id, "id");

        Response::ok($item)->send();
    }

    public static function deleteItem()
    {
        $id = $_GET["id"] ?? null;

        $deletionResult = MediaService::deleteItem($id);

        Response::ok($deletionResult)->send();
    }
}
