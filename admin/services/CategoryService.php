<?php

class CategoryService
{
    private $errors = [];

    public function create($data)
    {
        $category = self::findOne($data["slug"], "slug");

        if ($category) {
            $this->errors["dublicate_slug"] = "Тази категория вече е създадена.";
            return false;
        }

        $newCategory = [
            "title" => $data["title"],
            "slug" => $data["slug"],
            "description" => $data["description"],
            "thumbnail" => $data["thumbnail"],
        ];

        global $database;

        try {
            $database->insert("categories", $newCategory);
        } catch (Exception $ex) {
            echo "Insert category error: " . $ex->getMessage();
        }

        return $this->findOne($database->lastInsertedId());
    }

    public function findAll()
    {
        global $database;

        try {
            $categories = $database->getAll("SELECT * FROM categories");

            foreach ($categories as &$category) {
                unset($category["password"]);
            }

            return $categories;
        } catch (Exception $ex) {
            echo "Find all categories error: " . $ex->getMessage();
        }
    }

    public function findOne($value, $column = "id", $fields = "*")
    {
        global $database;

        $sql = "SELECT $fields FROM categories WHERE $column = :$column";
        $params = [":$column" => $value];

        try {
            $category = $database->getOne($sql, $params);
            return $category;
        } catch (Exception $ex) {
            echo "Find single category error: " . $ex->getMessage();
        }
    }

    public function getErrors() {
        return $this->errors;
    }
}
