<?php

use Jchook\Uuid;

class OrderService
{
    public static function create($data, $amount, $orderId)
    {
        global $database;

        try {
            $newOrder = [
                "id" => $orderId,
                "email" => $data->email,
                "delivery_address" => $data->delivery_address,
                "first_name" => $data->first_name,
                "last_name" => $data->last_name,
                "state" => $data->state,
                "city" => $data->city,
                "phone_number" => $data->phone_number,
                "invoice" => $data->invoice,
                "notice" => $data->notice,
                "amount" => $amount,
                "order_no" => time(),
            ];

            $database->insert("orders", $newOrder);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function prepareAndCreateOrder($user, $data, $orderId)
    {
        if ($user) {
            self::authenticatedUser($user, $data, $orderId);
        } else {
            self::guestUser($data, $orderId);
        }
    }

    public static function authenticatedUser($user, $data, $orderId)
    {
        $cartProducts = CartService::getCartItems($user["id"], "id, title, user_id, product_id, quantity, price");

        if (count($cartProducts) == 0) {
            Response::badRequest(["empty_cart" => "Нямате продукти в кошницата"])->send();
        }

        $amount = 0;

        foreach ($cartProducts as $cartProduct) {
            $amount += intval($cartProduct["quantity"]) * floatval($cartProduct["price"]);

            self::saveOrderProduct(
                $cartProduct["title"],
                $cartProduct["price"],
                $cartProduct["product_id"],
                $cartProduct["quantity"],
                $orderId
            );

            ProductService::decreaseQuantity($cartProduct["product_id"], $cartProduct["quantity"]);
        }

        OrderService::create($data, $amount, $orderId);
    }

    public static function guestUser($data, $orderId)
    {
        $sessionProducts = $_SESSION[CartService::$cart];

        if (count($sessionProducts) === 0) {
            Response::badRequest(["empty_cart" => "Нямате продукти в кошницата"])->send();
        }

        $amount = 0;

        foreach($sessionProducts as $sessionProduct) {
            $amount += floatval($sessionProduct["price"]) * intval($sessionProduct["quantity"]);
            self::saveOrderProduct(
                $sessionProduct["title"],
                $sessionProduct["price"],
                $sessionProduct["product_id"],
                $sessionProduct["quantity"],
                $orderId
            );

            ProductService::decreaseQuantity($sessionProduct["product_id"], $sessionProduct["quantity"]);
        }
        
        OrderService::create($data, $amount, $orderId);
    }

    public static function getItem($id)
    {
        global $database;

        $sql = "SELECT * FROM orders WHERE id = :id";
        $params = [
            ":id" => $id,
        ];

        try {
            $order = $database->getOne($sql, $params);
            return $order;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function saveOrderProduct($title, $price, $productId, $quantity, $orderId)
    {
        global $database;

        try {
            $data = [
                "title" => $title,
                "price" => $price,
                "product_id" => $productId,
                "quantity" => $quantity,
                "order_id" => $orderId,
            ];

            $database->insert("order_products", $data);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function getOrderProducts($orderId)
    {
        global $database;

        $params = [
            ":order_id" => $orderId
        ];

        try {
            $products = $database->getAll("SELECT * FROM order_products WHERE order_id = :order_id", $params);
            return $products;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function sendNewOrderEmail($email, $cartProducts, $amount)
    {
        $html = "";

        foreach ($cartProducts as $cartProduct) {
            $html .= "<tr>\n";
            $html .= "<td>" . $cartProduct["title"] . "</td>\n";
            $html .= "<td>" . $cartProduct["quantity"] . "</td>\n";
            $html .= "<td>" . $cartProduct["price"] . "</td>\n";
            $html .= "<td>" . $cartProduct["amount"] . "</td>\n";
            $html .= "</tr>\n";
        }

        $variables = [
            "rows" => $html,
            "amount" => $amount,
        ];

        $html = file_get_contents("email-templates/completed-order.html");
        $processedHtml = HTMLTemplateProcessor::replaceVariables($html, $variables);

        $mail = new Mail($email, "Нова поръчка", $processedHtml);
        $mail->send();
    }
}
