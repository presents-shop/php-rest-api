<?php

class ProductController
{
    // GET METHODS
    public static function saveItem()
    {
        $data = getJSONData();

        $id = $data->id ?? null;
        
        if (!$id) {
            $product = ProductService::create($data);
        } else {
            $product = ProductService::update($id, $data);
        }

        $product["media"] = self::getItemOptions($product,
        [
            "with_thumbnail" => true,
            "with_additional_images" => true,
        ]);

        Response::ok($product)->send();
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
        $product["media"] = self::getItemOptions($product,
        [
            "with_thumbnail" => $_GET["with_thumbnail"] ?? false,
            "with_additional_images" => $_GET["with_additional_images"] ?? false,
        ]);

        if (!$product) {
            Response::badRequest(["invalid_id" => "Този продукт не съществува"])->send();
        }

        Response::ok($product)->send();
    }

    public static function getItemOptions($product, $params)
    {
        $options = [];

        if (!empty($params["with_thumbnail"]) && $product["thumbnail_id"]) {
            $options["thumbnail"] = MediaService::findOne($product["thumbnail_id"]);
        }

        if (!empty($params["with_additional_images"]) && $product["additional_image_ids"]) {
            $additionalImages = [];
            
            foreach($product["additional_image_ids"] as $id) {
                $additionalImages[] = MediaService::findOne($id);
            }

            $options["additional_images"] = $additionalImages;
        }

        return $options;
    }

    public static function getItems()
    {
        $page = $_GET["page"] ?? 1;
        $limit = $_GET["limit"] ?? 5;
        $search = $_GET["search"] ?? null;
        $sort = $_GET["sort"] ?? null;

        $offset = ($page - 1) * $limit;

        $products = ProductService::findAll($offset, $limit, $search, $sort);
        $length = ProductService::getItemsLength();

        foreach($products as &$product) {
            $product["media"] = self::getItemOptions($product, ["with_thumbnail" => true]);
        }

        Response::ok([
            "items" => $products,
            "length" => $length,
            "params" => [
                "page" => intval($page),
                "limit" => intval($limit),
                "search" => $search,
                "sort" => $sort,
            ]
        ])->send();
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

        foreach ($data->media_ids as $id) {
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
