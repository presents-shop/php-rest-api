<?php

class CategoryService
{
    public static function create($data)
    {
        // Проверява дали е зададен "slug" за категорията
        if (!empty($data->slug)) {
            // Търси дали вече съществува категория със същия "slug"
            $category = self::findOne($data->slug, "slug");

            // Ако съществува категория със същия "slug", връща грешка 400 (Bad Request)
            if ($category) {
                Response::badRequest("Този адрес вече е използван за друга категория.")->send();
                return; // Добавено е return за да спре изпълнението
            }
        }

        // Кодиране на options в JSON формат, ако съществуват, или празен масив, ако няма такива
        $metaOptions = json_encode($data->options ?? []);

        // Подготовка на новата категория с данните от $data
        $newCategory = [
            "id" => $data->id,
            "title" => $data->title,                    // Заглавие на категорията
            "slug" => $data->slug,                     // Slug на категорията
            "thumbnail_id" => $data->thumbnail_id ?? null,     // ID на изображението на категорията или null
            "description" => $data->description ?? null,      // Описание на категорията или null
            "options" => $metaOptions,                    // JSON кодирани meta options или празен масив
            "parent_id" => $data->parent_id ?? null         // ID на родителската категория или null
        ];

        // Взема глобалния обект за връзка с базата данни
        global $database;

        // Вмъква новата категория в таблицата "categories"
        $database->insert("categories", $newCategory);

        // Връща новосъздадената категория, като я намира по последно вмъкнатото ID
        return self::findOne($data->id);
    }

    public static function findOne($value, $column = "id", $fields = "*")
    {
        global $database;

        $sql = "SELECT $fields FROM categories WHERE $column = :$column";
        $params = [":$column" => $value];

        $category = $database->getOne($sql, $params);

        if (!empty($category["options"])) {
            $category["options"] = json_decode($category["options"]);
        }
        if (!empty($category["additional_image_ids"])) {
            $category["additional_image_ids"] = json_decode($category["additional_image_ids"]);
        }

        return $category;
    }

    public static function update($id, $data)
    {
        // Взема глобалния обект за връзка с базата данни
        global $database;

        // Намира съществуващата категория по даденото ID
        $category = self::findOne($id);

        // Ако категорията не съществува, връща грешка 400 (Bad Request) и прекратява изпълнението
        if (!$category) {
            Response::badRequest("Тази категория не съществува")->send();
            return; // Добавено е return за да спре изпълнението
        }

        // Проверява дали slug е зададен. Ако не е, връща грешка 400 (Bad Request) и прекратява изпълнението
        if (empty($data->slug)) {
            Response::badRequest("Не можете да запазите категория с празен URL адрес.")->send();
            return; // Добавено е return за да спре изпълнението
        }

        // Търси категория със същия slug
        $categoryBySlug = self::findOne($data->slug, "slug");

        // Проверява дали вече съществува категория със същия slug и дали тя не е текущата категория
        if ($categoryBySlug && $categoryBySlug["id"] != $data->id) {
            Response::badRequest("Вече съществува друга категория с този URL адрес.")->send();
            return; // Добавено е return за да спре изпълнението
        }

        // Кодиране на options в JSON формат, ако съществуват, или празен масив, ако няма такива
        $metaOptions = json_encode($data->options) ?? [];

        // Подготовка на актуализираните данни за категорията
        $newCategory = [
            "title"         => $data->title ?? null,          // Заглавие на категорията или null, ако не е зададено
            "slug"          => $data->slug ?? null,           // Slug на категорията или null, ако не е зададено
            "description"   => $data->description ?? null,    // Описание на категорията или null
            "thumbnail_id"  => $data->thumbnail_id ?? null,   // ID на изображението на категорията или null
            "options"  => $metaOptions,                  // JSON кодирани meta options или празен масив
            "parent_id"     => $data->parent_id ?? null       // ID на родителската категория или null
        ];

        // Актуализиране на категорията в базата данни по даденото ID
        $database->update("categories", $newCategory, "id = '$id'");

        // Връща актуализираната категория, като я намира по ID
        return self::findOne($id);
    }

    public static function delete($id)
    {
        global $database;

        $category = self::findOne($id);

        if (!$category) {
            Response::badRequest("Невалиден идентификатор на категория")->send();
        }

        $hasProducts = ProductService::findOne($id, "category_id");

        if (!empty($hasProducts)) {
            Response::badRequest("Не можете да изтриете тази категория, защото съдържа един или повече продукти.")->send();
        }

        return $database->delete("categories", "id = '$id'", []);
    }

    public static function findAll($offset, $limit, $search, $sort, $parentId)
    {
        global $database;

        $query = self::buildSQLQueryForFindAll($offset, $limit, $search, $sort, $parentId);

        $categories = $database->getAll($query["sql"], $query["params"]);

        foreach ($categories as &$category) {
            if (!empty($category["options"])) {
                $category["options"] = json_decode($category["options"]);
            }

            if (!empty($category["additional_image_ids"])) {
                $category["additional_image_ids"] = json_decode($category["additional_image_ids"]);
            }
        }

        return $categories;
    }

    public static function buildSQLQueryForFindAll($offset, $limit, $search, $sort, $parentId)
    {
        $sql = "SELECT * FROM categories";

        $params = [];
        $conditions = [];

        // Проверка за търсене
        if ($search) {
            $conditions[] = "title LIKE '%$search%'";
        }

        // Проверка за parentId
        if (!$parentId && !$search) {
            $conditions[] = "parent_id IS NULL";
        } else if ($parentId) {
            $conditions[] = "parent_id = '$parentId'";
        }

        // Добавяне на условията към заявката
        if (count($conditions) > 0) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        // Проверка за сортиране
        if ($sort == "asc" || $sort == "desc") {
            $sql .= " ORDER BY title $sort";
        } elseif ($sort == "new" || $sort == "old") {
            $method = $sort == "new" ? "DESC" : "ASC";
            $sql .= " ORDER BY id $method";
        }

        // Ограничаване на резултатите
        if ($limit) {
            $sql .= " LIMIT $limit";
        }

        // Задаване на offset
        if ($offset) {
            $sql .= " OFFSET $offset";
        }

        return ["sql" => $sql, "params" => $params];
    }

    public static function getItemsLength($search, $parentId)
    {
        global $database;

        $sql = "SELECT COUNT(*) AS 'length' FROM categories";

        if ($search) {
            $conditions[] = "title LIKE '%$search%'";
        }

        if (!$parentId && !$search) {
            $conditions[] = "parent_id IS NULL";
        } else if ($parentId) {
            $conditions[] = "parent_id = '$parentId'";
        }

        if (count($conditions) > 0) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
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
            $database->update("categories", $updatedCategory, "id = '$id'");
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
