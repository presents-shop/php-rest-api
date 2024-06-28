<?php

class CategoryController
{
    // GET METHODS
    public static function getAll()
    {
        $category = new CategoryService();
        $categories = $category->findAll();

        $meta = new MetaTags(
            "Всички категории",
        );

        view("categories/index", ["meta" => $meta->getTags(), "categories" => $categories]);
    }

    public static function getCreate($errors = [], $success = false)
    {
        $meta = new MetaTags(
            "Създаване на нова категория",
        );

        $_SESSION["code"] = generateSecurityCode();

        view("admin/categories/create", [
            "meta" => $meta->getTags(),
            "errors" => [...$errors],
            "success" => $success,
            "input" => $_POST,
            "files" => $_FILES,
            "code" => $_SESSION["code"],
        ]);
    }

    // RENDER METHODS
    public static function create()
    {
        $errors = CategoryValidation::create($_POST);

        if (count($errors) > 0) {
            self::getCreate($errors);
        }

        $category = new CategoryService();
        $id = $category->create($_POST);

        if (!$id) {
            self::getCreate($category->getErrors());
        }

        self::getCreate([], true);
    }
}