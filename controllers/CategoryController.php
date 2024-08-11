<?php

class CategoryController
{
    // POST ROUTES
    public static function saveItem()
    {
        // Уверява се, че потребителят е автентикиран преди да продължи
        AuthGuard::authenticated();

        // Получаване на данните от заявката в JSON формат
        $data = getJSONData();

        // Валидиране на данните от заявката
        $error = CategoryValidation::saveItem($data);

        // Проверка дали има грешка в данните, ако има такава, връща съобщението на грешката със 400 (Bad Request)
        if (is_string($error)) {
            Response::badRequest($error)->send();
            return; // Добавено е return за да спре изпълнението
        }

        // Проверка дали ID-то е зададено и дали е валидно (т.е. дали е низ)
        $id = isset($data->id) ? $data->id : null;

        // Ако ID-то е празно или не е низ, връща грешка 400 (Bad Request) с подходящо съобщение
        if (empty($id) || !is_string($id)) {
            Response::badRequest("Моля, генерирайте уникален идентификатор на категорията.")->send();
            return; // Добавено е return за да спре изпълнението
        }

        // Ако е зададено родителско ID, проверява дали родителската категория съществува
        if (!empty($data->parent_id)) {
            $parentCategory = CategoryService::findOne($data->parent_id);

            // Ако родителската категория не съществува, връща грешка 400 (Bad Request) с подходящо съобщение
            if (empty($parentCategory)) {
                Response::badRequest("Невалиден идентификатор на родителска категория.")->send();
                return; // Добавено е return за да спре изпълнението
            }
        }

        // Търсене на съществуваща категория по ID
        $category = CategoryService::findOne($id);

        // Ако категорията не съществува, създава нова; в противен случай я актуализира
        if (empty($category)) {
            $category = CategoryService::create($data);
        } else {
            $category = CategoryService::update($id, $data);
        }

        // Връща успешен отговор със статус 201 (Created) и новата/актуализираната категория
        Response::created($category)->send();
    }

    public static function deleteItem()
    {
        // AuthGuard::authenticated();

        $id = $_GET["id"];

        $result = CategoryService::delete($id);

        Response::ok($result)->send();
    }

    public static function getItem()
    {
        $column = $_GET["column"] ?? null;
        $value = $_GET["value"] ?? null;

        $category = CategoryService::findOne($value, $column);

        $category["media"] = self::getItemOptions(
            $category,
            [
                "with_thumbnail" => $_GET["with_thumbnail"] ?? false,
            ]
        );

        if (!$category) {
            Response::badRequest(["invalid_id" => "Тази категория не съществува"])->send();
        }

        Response::ok($category)->send();
    }

    public static function getItemOptions($category, $params)
    {
        $options = [];

        if (!empty($params["with_thumbnail"]) && $category["thumbnail_id"]) {
            $options["thumbnail"] = MediaService::findOne($category["thumbnail_id"]);
        }

        if (!empty($params["with_additional_images"]) && $category["additional_image_ids"]) {
            $additionalImages = [];

            foreach ($category["additional_image_ids"] as $id) {
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
        $parentId = $_GET["parent_id"] ?? null;

        $offset = ($page - 1) * $limit;

        $categories = CategoryService::findAll($offset, $limit, $search, $sort, $parentId);
        $length = CategoryService::getItemsLength($search);

        foreach ($categories as &$category) {
            $category["media"] = self::getItemOptions($category, ["with_thumbnail" => true]);
        }

        Response::ok([
            "items" => $categories,
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
        AuthGuard::authenticated();

        $data = getJSONData();

        CategoryValidation::saveThumbnail($data);

        $category = CategoryService::findOne($data->category_id);

        if (!$category) {
            Response::badRequest($category)->send();
        }

        $image = MediaService::findOne($data->media_id);

        if (!$image) {
            Response::badRequest($image)->send();
        }

        $category = CategoryService::saveThumbnail($data);

        Response::ok($category)->send();
    }

    public static function saveAdditionalImages()
    {
        AuthGuard::authenticated();

        $data = getJSONData();

        CategoryValidation::saveAdditionalImages($data);

        $category = CategoryService::findOne($data->category_id);

        if (!$category) {
            Response::badRequest($category)->send();
        }

        foreach ($data->media_ids as $id) {
            $image = MediaService::findOne($id);

            if (!$image) {
                Response::badRequest($image)->send();
            }
        }

        $category = CategoryService::saveAdditionalImages($data);

        Response::ok($category)->send();
    }
}