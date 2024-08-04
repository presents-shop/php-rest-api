<?php

class CategoryService
{
    public static function create($data)
    {
        if (!empty($data->slug)) {
            $category = self::findOne($data->slug, "slug");
    
            if ($category) {
                Response::badRequest("Този адрес вече е използван за друга категория.")->send();
            }
        }

        $metaOptions = json_encode($data->meta_options) ?? [];

        $newCategory = [
            "title" => $data->title ?? null,
            "slug" => $data->slug ?? null,
            "thumbnail_id" => $data->thumbnail_id ?? null,
            "description" => $data->description ?? null,
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
            }
            if (!empty($category["additional_image_ids"])) {
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
            Response::badRequest("Тази категория не съществува")->send();
        }

        if (empty($data->slug)) {
            Response::badRequest("Не можете да запазите категорията с празен URL адрес.")->send();
        }

        $categoryBySlug = self::findOne($data->slug, "slug");

        if ($categoryBySlug && $categoryBySlug["id"] != $data->id) {
            Response::badRequest("Вече съществува друга категорията с този URL адрес.")->send();
        }

        $metaOptions = json_encode($data->meta_options) ?? [];

        $newCategory = [
            "title" => $data->title ?? null,
            "slug" => $data->slug ?? null,
            "description" => $data->description ?? null,
            "thumbnail_id" => $data->thumbnail_id ?? null,
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

    public static function findAll($offset, $limit, $search, $sort)
    {
        global $database;

        $sql = "SELECT * FROM categories";

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
            $categories = $database->getAll($sql, []);

            foreach ($categories as &$category) {
                if (!empty($category["meta_options"])) {
                    $category["meta_options"] = json_decode($category["meta_options"]);
                }

                if (!empty($category["additional_image_ids"])) {
                    $category["additional_image_ids"] = json_decode($category["additional_image_ids"]);
                }
            }

            return $categories;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function getItemsLength($search)
    {
        global $database;

        $sql = "SELECT COUNT(*) AS 'length' FROM categories";

        if ($search) {
            $sql .= " WHERE title LIKE '%$search%'";
        }

        try {
            $data = $database->getOne($sql, []);
            return $data["length"] ?? 0;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    public static function saveThumbnail($data)
    {
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

    public static function saveAdditionalImages($data)
    {
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
