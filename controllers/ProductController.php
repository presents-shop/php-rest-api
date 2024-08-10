<?php

class ProductController
{
    // GET METHODS
    public static function saveItem()
    {
        $data = getJSONData();

        $error = ProductValidation::saveItem($data);

        if (is_string($error)) {
            Response::badRequest($error)->send();
            return;
        }

        $id = isset($data->id) ? $data->id : null;

        if (empty($id) || !is_string($id)) {
            Response::badRequest("Моля, генерирайте уникален идентификатор на продукта.")->send();
            return;
        }

        $product = ProductService::findOne($id);

        if (empty($product)) {
            $product = ProductService::create($data);
            if (!$product) {
                Response::serverError("Грешка при създаване продукта.")->send();
            }
        } else {
            $product = ProductService::update($id, $data);
            if (!$product) {
                Response::serverError("Грешка при редактиране на продукта.")->send();
            }
        }

        $params = [
            "with_thumbnail" => true,
            "with_additional_images" => true,
        ];

        $product["media"] = self::getItemOptions($product, $params);

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
        $id = $_GET["id"] ?? null;

        if (empty($id)) {
            Response::badRequest("Невалиден идентификатор.")->send();
            return;
        }

        $product = ProductService::findOne($id);

        if (!$product) {
            Response::badRequest("Този продукт не съществува.")->send();
            return;
        }

        $params = [
            "with_thumbnail" => isset($_GET["with_thumbnail"]) ? filter_var($_GET["with_thumbnail"], FILTER_VALIDATE_BOOLEAN) : false,
            "with_additional_images" => isset($_GET["with_additional_images"]) ? filter_var($_GET["with_additional_images"], FILTER_VALIDATE_BOOLEAN) : false,
        ];

        $product["media"] = self::getItemOptions($product, $params);

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
        $page = isset($_GET["page"]) ? $_GET["page"] : 1;
        $limit = isset($_GET["limit"]) ? $_GET["limit"] : 5;
        $search = isset($_GET["search"]) ? $_GET["search"] : '';
        $sort = isset($_GET["sort"]) ? $_GET["sort"] : '';

        if ($page < 1) $page = 1;
        if ($limit < 1) $limit = 5;

        $offset = ($page - 1) * $limit;

        $products = ProductService::findAll($offset, $limit, $search, $sort);
        $length = ProductService::getItemsLength();

        foreach ($products as &$product) {
            $params = [
                "with_thumbnail" => true,
            ];

            $product["media"] = self::getItemOptions($product, $params);
        }

        Response::ok([
            "items" => $products,
            "length" => $length,
            "params" => [
                "page" => $page,
                "limit" => $limit,
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
