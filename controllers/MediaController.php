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

        $offset = ($page - 1) * $limit;

        $mediaFiles = MediaService::findAll($offset, $limit);

        Response::ok($mediaFiles)->send();
    }
}