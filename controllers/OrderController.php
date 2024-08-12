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
}