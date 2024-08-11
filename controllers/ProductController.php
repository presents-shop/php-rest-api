<?php

require "populators/ProductPopulator.php";
require "managers/products/SaveItemProductManager.php";

class ProductController
{
    // GET METHODS
    public static function saveItem() {
        $data = getJSONData();
        $product = SaveItemProductManager::saveItem($data);
        Response::ok($product)->send();
    }

    public static function deleteItem()
    {
        AuthGuard::authenticated();

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
            "with_category" => isset($_GET["with_category"]) ? filter_var($_GET["with_category"], FILTER_VALIDATE_BOOLEAN) : false,
            "with_thumbnail" => isset($_GET["with_thumbnail"]) ? filter_var($_GET["with_thumbnail"], FILTER_VALIDATE_BOOLEAN) : false,
            "with_additional_images" => isset($_GET["with_additional_images"]) ? filter_var($_GET["with_additional_images"], FILTER_VALIDATE_BOOLEAN) : false,
        ];

        $product = ProductPopulator::populateDependencies($product, $params);

        Response::ok($product)->send();
    }

    public static function getItems()
    {
        $page = isset($_GET["page"]) ? $_GET["page"] : 1;
        $limit = isset($_GET["limit"]) ? $_GET["limit"] : 5;
        $search = isset($_GET["search"]) ? $_GET["search"] : "";
        $sort = isset($_GET["sort"]) ? $_GET["sort"] : "";
        $categoryId = isset($_GET["category_id"]) ? $_GET["category_id"] : "";

        if ($page < 1)
            $page = 1;
        if ($limit < 1)
            $limit = 5;

        $offset = ($page - 1) * $limit;

        $products = ProductService::findAll($offset, $limit, $search, $sort, $categoryId);
        $length = ProductService::getItemsLength();

        foreach ($products as &$product) {
            $params = [
                "with_category" => true,
                "with_thumbnail" => true,
            ];

            $product = ProductPopulator::populateDependencies($product, $params);
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