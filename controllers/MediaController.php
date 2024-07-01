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
}
