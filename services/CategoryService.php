<?php

class CategoryService
{
    public static function create($data)
    {
        $category = self::findOne($data->slug, "slug");

        if ($category) {
            Response::badRequest(["dublicate_slug" => "Този адрес вече е използван за друга категория."])->send();
        }

        $metaOptions = json_encode($data->meta_options) ?? [];

        $newCategory = [
            "title" => $data->title,
            "slug" => $data->slug,
            "description" => $data->description,
            "meta_options" => $metaOptions,
        ];

        global $database;

        try {
            $database->insert("categories", $newCategory);
            return self::findOne($database->lastInsertedId());
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function findOne($value, $column = "id", $fields = "*")
    {
        global $database;

        $sql = "SELECT $fields FROM categories WHERE $column = :$column";
        $params = [":$column" => $value];

        try {
            $category = $database->getOne($sql, $params);

            if (!empty($category["meta_options"])) {
                $category["meta_options"] = json_decode($category["meta_options"]);
                $category["additional_image_ids"] = json_decode($category["additional_image_ids"]);
            }

            return $category;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function update($id, $data)
    {
        global $database;

        $category = self::findOne($id);

        if (!$category) {
            Response::badRequest(["invalid_id" => "Тази категория не съществува"])->send();
        }

        $metaOptions = json_encode($data->meta_options) ?? [];

        $newCategory = [
            "title" => $data->title,
            "description" => $data->description,
            "meta_options" => $metaOptions,
        ];

        try {
            $database->update("categories", $newCategory, "id = $id");
            return self::findOne($id);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function delete($id)
    {
        global $database;

        $category = self::findOne($id);

        if (!$category) {
            Response::badRequest(["invalid_id" => "Тази категория не съществува"])->send();
        }

        try {
            return $database->delete("categories", "id = $id", []);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function findAll($offset, $limit)
    {
        global $database;

        $sql = "SELECT * FROM categories";

        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        if ($offset) {
            $sql .= " OFFSET $offset";
        }

        try {
            $categories = $database->getAll($sql, []);

            foreach($categories as &$category) {
                $category["meta_options"] = json_decode($category["meta_options"]);
                $category["additional_image_ids"] = json_decode($category["additional_image_ids"]);
            }

            return $categories;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function saveThumbnail($data) {
        global $database;

        $id = $data->category_id;

        try {
            $updatedCategory = ["thumbnail_id" => $data->media_id];
            $database->update("categories", $updatedCategory, "id = $id");
            return self::findOne($id);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function saveAdditionalImages($data) {
        global $database;

        $id = $data->category_id;

        try {
            $updatedCategory = ["additional_image_ids" => json_encode($data->media_ids)];
            $database->update("categories", $updatedCategory, "id = $id");
            return self::findOne($id);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
}
