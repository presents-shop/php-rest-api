<?php

require "vendor/autoload.php";

use Jchook\Uuid;

class OrderController
{
    public static function create()
    {
        $data = getJSONData();

        global $database;
        $orderId = Uuid::v4();

        try {
            $database->beginTransaction();

            $user = UserService::isAuthenticated();

            OrderService::prepareAndCreateOrder($user, $data, $orderId);

            $order = OrderService::getItem($orderId);

            $order["ordered_products"] = OrderService::getOrderProducts($orderId);
            
            $products = [];
            
            if ($user) {
                $products = CartService::getCartItems($user["id"], "id, title, user_id, product_id, quantity, price, amount");
                CartService::deleteItemsByUser($user["id"]);
            } else {
                $products = $_SESSION[CartService::$cart];
                $_SESSION[CartService::$cart] = [];
            }

            OrderService::sendNewOrderEmail($order["email"], $products, $order["amount"]);

            $database->commit();
            
            Response::created($order)->send();
        } catch(Exception $ex) {
            $database->rollBack();
            throw new Exception($ex->getMessage());
        }
    }
}