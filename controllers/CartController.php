<?php

use Jchook\Uuid;

class CartController
{
    public static function saveItem()
    {
        $data = getJSONData();

        $columns = "id, quantity, original_price, selling_price";
        $product = ProductService::findOne($data->product_id, "id", $columns);

        if (!$product) {
            Response::badRequest(["invalid_product_id" => "Невалидно id на продукт"])->send();
        }

        $user = UserService::isAuthenticated();

        if (!$user) {
            CartService::saveItemToSession($product, $data);
            Response::ok($_SESSION[CartService::$cart])->send();
        } else {
            $userId = $user["id"];

            try {
                if ($data->quantity == 0) {
                    CartService::deleteItem($data, $userId);
                } else {
                    CartService::saveCartItem($data, $userId, $product);
                }

                $cartItems = CartService::getCartItems($userId);
                Response::ok($cartItems)->send();
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage());
            }
        }
    }

    public static function getItems()
    {
        $user = UserService::isAuthenticated();

        if (!$user) {
            Response::ok($_SESSION[CartService::$cart])->send();
        } else {
            $cartItems = CartService::getCartItems($user["id"]);
            Response::ok($cartItems)->send();
        }
    }
}