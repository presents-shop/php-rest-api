<?php

class ProductController
{
    // GET METHODS
    public static function saveItem()
    {
        $data = getJSONData();

        ProductValidation::saveItem($data);

        $id = $data->id ?? null;

        if (!$id) {
            $product = ProductService::create(getJSONData());
        } else {
            $product = ProductService::update($id, getJSONData());
        }

        Response::created($product)->send();
    }

    public static function deleteItem()
    {
        $id = $_GET["id"];

        $result = ProductService::delete($id);

        Response::ok($result)->send();
    }

    public static function getItem()
    {
        $id = $_GET["id"];

        $product = ProductService::findOne($id);

        if (!$product) {
            Response::badRequest(["invalid_id" => "Този продукт не съществува"])->send();
        }

        Response::ok($product)->send();
    }

    public static function getItems()
    {
        $page = $_GET["page"] ?? 1;
        $limit = $_GET["limit"] ?? null;

        $offset = ($page - 1) * $limit;

        $products = ProductService::findAll($offset, $limit);

        Response::ok($products)->send();
    }

    public static function saveThumbnail()
    {
        $data = getJSONData();

        ProductValidation::saveThumbnail($data);

        $product = ProductService::findOne($data->product_id);

        if (!$product) {
            Response::badRequest($product)->send();
        }

        $image = MediaService::findOne($data->media_id);

        if (!$image) {
            Response::badRequest($image)->send();
        }

        $product = ProductService::saveThumbnail($data);

        Response::ok($product)->send();
    }

    public static function saveAdditionalImages()
    {
        $data = getJSONData();

        ProductValidation::saveAdditionalImages($data);

        $product = ProductService::findOne($data->product_id);

        if (!$product) {
            Response::badRequest(["invalid_id" => "Този продукт не съществува"])->send();
        }

        foreach($data->media_ids as $id) {
            $image = MediaService::findOne($id);

            if (!$image) {
                Response::badRequest(["invalid_image_id" => "Всички id-та трябва да съдествуват"])->send();
            }
        }

        $product = ProductService::saveAdditionalImages($data);

        Response::ok($product)->send();
    }

    public static function saveCategory()
    {
        $data = getJSONData();

        ProductValidation::saveCategory($data);

        $product = ProductService::findOne($data->product_id);

        if (!$product) {
            Response::badRequest(["invalid_id" => "Този продукт  не съществува"])->send();
        }

        $category = MediaService::findOne($data->category_id);

        if (!$category) {
            Response::badRequest(["invalid_id" => "Тази категория не съществува"])->send();
        }

        $product = ProductService::saveCategory($data);

        Response::ok($product)->send();
    }
}