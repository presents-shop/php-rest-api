<?php

class ProductService
{
    public static function create($data)
    {
        $product = self::findOne($data->slug, "slug");

        if ($product) {
            Response::badRequest("Този адрес вече е използван за друг продукт.")->send();
            return false;
        }

        $product = self::findOne($data->sku, "sku");

        if ($product) {
            Response::badRequest("Този код вече е използван за друг продукт.")->send();
            return false;
        }

        $newProduct = [
            "id" => $data->id,
            "name" => $data->name,
            "slug" => $data->slug,
            "sku" => $data->sku,
            "description" => $data->description ?? null,
            "category_id" => $data->category_id ?? null,
            "original_price" => $data->original_price ?? null,
            "selling_price" => $data->selling_price ?? null,
            "is_promo" => $data->is_promo ?? 0,
            "is_free" => $data->is_free ?? 0,
            "quantity" => $data->quantity,
            "supplier_id" => $data->supplier_id ?? null,
            "weight" => $data->weight,
            "dimensions" => $data->dimensions,
            "color" => $data->color ?? null,
            "material" => $data->material ?? null,
            "is_featured" => $data->is_featured ?? 0,
            "tags" => $data->tags ?? null,
            "thumbnail_id" => $data->thumbnail_id ?? null,
            "additional_image_ids" => json_encode($data->additional_image_ids ?? []),
            "options" => json_encode($data->options ?? []),
        ];

        global $database;

        try {
            $database->insert("products", $newProduct);
            return self::findOne($newProduct["id"]);
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

            if (!empty($product["options"])) {
                $product["options"] = json_decode($product["options"]);
            }

            if (!empty($product["additional_image_ids"])) {
                $product["additional_image_ids"] = json_decode($product["additional_image_ids"]);
            }

            return $product;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function update($id, $data)
    {
        global $database;

        $product = self::findOne($data->slug, "slug");

        if ($product) {
            Response::badRequest("Този адрес вече е използван за друг продукт.")->send();
            return false;
        }

        $product = self::findOne($data->sku, "sku");

        if ($product) {
            Response::badRequest("Този код вече е използван за друг продукт.")->send();
            return false;
        }

        $updatedProduct = [
            "name" => $data->name,
            "slug" => $data->slug,
            "sku" => $data->sku,
            "description" => $data->description ?? null,
            "category_id" => $data->category_id ?? null,
            "original_price" => $data->original_price ?? null,
            "selling_price" => $data->selling_price ?? null,
            "is_promo" => $data->is_promo ?? 0,
            "is_free" => $data->is_free ?? 0,
            "quantity" => $data->quantity,
            "supplier_id" => $data->supplier_id ?? null,
            "weight" => $data->weight,
            "dimensions" => $data->dimensions,
            "color" => $data->color ?? null,
            "material" => $data->material ?? null,
            "is_featured" => $data->is_featured ?? 0,
            "tags" => $data->tags ?? null,
            "thumbnail_id" => $data->thumbnail_id ?? null,
            "additional_image_ids" => json_encode($data->additional_image_ids ?? []),
            "options" => json_encode($data->options ?? []),
        ];

        try {
            $database->update("products", $updatedProduct, "id = '$id'");
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
            $database->update("products", $newProduct, "id = '$id'");
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
            return $database->delete("products", "id = '$id'", []);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function findAll($offset, $limit, $search, $sort)
    {
        global $database;

        $sql = "SELECT * FROM products";

        if ($sort == "asc" || $sort == "desc") {
            $sql .= " ORDER BY title $sort";
        }

        if ($sort == "new" || $sort == "old") {
            $method = $sort == "new" ? "desc" : "asc";
            $sql .= " ORDER BY id $method";
        }

        if ($search) {
            $sql .= " WHERE title LIKE '%$search%'";
        }

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

    public static function getItemsLength()
    {
        global $database;

        try {
            $data = $database->getOne("SELECT COUNT(*) AS 'length' FROM products");
            return $data["length"];
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
            $database->update("products", $updatedProduct, "id = '$id'");
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
            $database->update("products", $updatedProduct, "id = '$id'");
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
            $database->update("products", $updatedProduct, "id = '$id'");
            return self::findOne($id);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
}