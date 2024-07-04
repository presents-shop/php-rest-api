<?php

require "vendor/autoload.php";

use Jchook\Uuid;

class ProductService
{
    public static function create($data)
    {
        $product = self::findOne($data->slug, "slug");

        if ($product) {
            Response::badRequest(["dublicate_slug" => "Този адрес вече е използван за друг продукт."])->send();
        }

        $metaOptions = json_encode($data->meta_options) ?? [];
        $twitterOptions = json_encode($data->twitter_options) ?? [];
        $ogOptions = json_encode($data->og_options) ?? [];
        $productOptions = json_encode($data->product_options) ?? [];

        $newProduct = [
            "id" => Uuid::v4(),
            "title" => $data->title,
            "slug" => $data->slug,
            "short_description" => $data->short_description ?? null,
            "description" => $data->description ?? null,
            "original_price" => $data->original_price,
            "selling_price" => $data->selling_price ?? null,
            "quantity" => $data->quantity,
            "meta_options" => $metaOptions,
            "twitter_options" => $twitterOptions,
            "og_options" => $ogOptions,
            "product_options" => $productOptions,
        ];

        global $database;

        try {
            $database->insert("products", $newProduct);
            return self::findOne($database->lastInsertedId());
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function findOne($value, $column = "id", $fields = "*")
    {
        global $database;

        $sql = "SELECT $fields FROM products WHERE $column = :$column";
        $params = [":$column" => $value];

        try {
            $product = $database->getOne($sql, $params);

            if (!empty($product["meta_options"])) {
                $product["meta_options"] = json_decode($product["meta_options"]);
            }

            if (!empty($product["product_options"])) {
                $product["product_options"] = json_decode($product["product_options"]);
            }

            if (!empty($product["og_options"])) {
                $product["og_options"] = json_decode($product["og_options"]);
            }

            if (!empty($product["twitter_options"])) {
                $product["twitter_options"] = json_decode($product["twitter_options"]);
            }

            if (!empty($product["additional_image_ids"])) {
                $product["additional_image_ids"] = json_decode($product["additional_image_ids"] ?? []);
            }

            return $product;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function update($id, $data)
    {
        global $database;

        $category = self::findOne($id);

        if (!$category) {
            Response::badRequest(["invalid_id" => "този продукт не съществува"])->send();
        }

        $metaOptions = json_encode($data->meta_options) ?? [];
        $twitterOptions = json_encode($data->twitter_options) ?? [];
        $ogOptions = json_encode($data->og_options) ?? [];
        $productOptions = json_encode($data->product_options) ?? [];

        $newProduct = [
            "title" => $data->title,
            "description" => $data->description ?? null,
            "short_description" => $data->short_description ?? null,
            "original_price" => $data->original_price,
            "selling_price" => $data->selling_price ?? null,
            "quantity" => $data->quantity,
            "category_id" => $data->category_id ?? null,
            "meta_options" => $metaOptions,
            "twitter_options" => $twitterOptions,
            "og_options" => $ogOptions,
            "product_options" => $productOptions,
        ];

        try {
            $database->update("products", $newProduct, "id = $id");
            return self::findOne($id);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function decreaseQuantity($id, $quantity)
    {
        global $database;

        $product = ProductService::findOne($id);

        $newProduct = [
            "quantity" => intval($product["quantity"]) - intval($quantity)
        ];
        
        try {
            $database->update("products", $newProduct, "id = $id");
            return self::findOne($id);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function delete($id)
    {
        global $database;

        $product = self::findOne($id);

        if (!$product) {
            Response::badRequest(["invalid_id" => "Този продукт не съществува"])->send();
        }

        try {
            return $database->delete("products", "id = $id", []);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function findAll($offset, $limit)
    {
        global $database;

        $sql = "SELECT * FROM products";

        if ($limit) {
            $sql .= " LIMIT $limit";
        }

        if ($offset) {
            $sql .= " OFFSET $offset";
        }

        try {
            $products = $database->getAll($sql, []);

            foreach ($products as &$product) {
                if (!empty($product["meta_options"])) {
                    $product["meta_options"] = json_decode($product["meta_options"]);
                }

                if (!empty($product["product_options"])) {
                    $product["product_options"] = json_decode($product["product_options"]);
                }

                if (!empty($product["og_options"])) {
                    $product["og_options"] = json_decode($product["og_options"]);
                }

                if (!empty($product["twitter_options"])) {
                    $product["twitter_options"] = json_decode($product["twitter_options"]);
                }

                if (!empty($product["additional_image_ids"])) {
                    $product["additional_image_ids"] = json_decode($product["additional_image_ids"] ?? []);
                }
            }

            return $products;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function saveThumbnail($data)
    {
        global $database;

        $id = $data->product_id;

        try {
            $updatedProduct = ["thumbnail_id" => $data->media_id];
            $database->update("products", $updatedProduct, "id = $id");
            return self::findOne($id);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function saveAdditionalImages($data)
    {
        global $database;

        $id = $data->product_id;

        try {
            $updatedProduct = ["additional_image_ids" => json_encode($data->media_ids)];
            $database->update("products", $updatedProduct, "id = $id");
            return self::findOne($id);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function saveCategory($data)
    {
        global $database;

        $id = $data->product_id;

        try {
            $updatedProduct = ["category_id" => json_encode($data->category_id)];
            $database->update("products", $updatedProduct, "id = $id");
            return self::findOne($id);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
}