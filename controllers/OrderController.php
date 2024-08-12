<?php

use Jchook\Uuid;

class OrderController
{
    public static function create()
    {
        // Проверка за автентикация на потребителя
        $user = UserService::isAuthenticated();
        $data = getJSONData();
        $productList = [];

        // Започване на транзакция
        global $database;
        $database->beginTransaction();

        try {
            // Актуализиране на количествата на продуктите
            OrderService::updateProductsQuantity($user);

            // Изчисляване на общата сума на поръчката
            $data->total_amount = OrderService::calculateTotalAmount($user);

            if ($user) {
                // Получаване на артикулите в количката на потребителя
                $productList = CartService::getCartItems($user["id"]);

                // Изтриване на артикулите от количката след създаване на поръчка
                CartService::deleteItemsByUser($user["id"]);
            } else {
                // Ако потребителят не е влязъл, използване на артикулите от сесията
                $productList = $_SESSION[CartService::$cart];

                // Създаване на нов потребител с предоставените имейл и телефон
                $newUser = [
                    "email" => $data->customer_email,
                    "phone" => $data->customer_phone,
                    "password" => Uuid::v4(),
                ];

                // Проверка дали съществува потребител с този имейл
                $user = UserService::findOne($data->customer_email, "email");

                // Регистрация на нов потребител, ако не е намерен
                if (!$user) {
                    $user = UserService::register((object) $newUser);
                }
            }

            // Добавяне на продуктовия списък към данните на поръчката
            $data->product_list = $productList;
            $data->customer_id = $user["id"];

            // Създаване на нова поръчка
            $newOrder = OrderService::create($data);

            // Изпращане на имейл, ако е указано в заявката
            if (!empty($_GET["send_email_processing"])) {
                OrderService::sendOrderEmail($newOrder, $_GET);
            }

            // Изчистване на количката в сесията, ако потребителят не е бил автентифициран
            if (!$user) {
                $_SESSION[CartService::$cart] = [];
            }

            // Потвърждаване на транзакцията
            $database->commit();

            // Връщане на отговор със статус 201 (създадено)
            Response::created($newOrder)->send();

        } catch (Exception $e) {
            // При грешка, връщане на транзакцията
            $database->rollBack();

            // Връщане на грешката като отговор
            Response::serverError($e->getMessage())->send();
        }
    }

    public static function getItem()
    {
        // 1. Извличане на ID-то на поръчката от заявката
        $id = $_GET["id"] ?? null;

        // 2. Проверка дали ID-то е валидно (не е празно)
        if (empty($id)) {
            Response::badRequest("Невалиден идентификатор на поръчка")->send();
            return;
        }

        // 3. Извличане на поръчката по ID чрез OrderService
        $order = OrderService::getItem($id);

        // 4. Проверка дали поръчката съществува
        if (!$order) {
            Response::notFound("Поръчката не е намерена")->send();
            return;
        }

        // 5. Обработка на списъка с продукти в поръчката
        foreach ($order["product_list"] as &$cartItem) {
            // Извличане на данните за продукта
            $product = ProductService::findOne($cartItem->cart_product_id);

            // Ако продуктът съществува, добавяне на данните за него към артикула в количката
            if ($product) {
                $cartItem = [
                    "cart_product" => $cartItem,
                    "product" => $product
                ];
            } else {
                // Ако продуктът не е намерен, задаване на стойност null
                $cartItem->product = null;
            }
        }

        // 6. Връщане на успешно извлечените данни за поръчката
        Response::ok($order)->send();
    }

    public static function getItems()
    {
        $page = $_GET["page"] ?? null;
        $limit = $_GET["limit"] ?? null;
        $status = $_GET["status"] ?? null;
        $offset = ($page - 1) * $limit;

        $orders = OrderService::getItems($offset, $limit, $status);

        Response::ok(["items" => $orders, "params" => $_GET])->send();
    }
}
