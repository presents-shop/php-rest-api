<?php

class CartController
{
    public static function saveItem()
    {
        $data = getJSONData();

        $product = ProductService::findOne($data->cart_product_id, "id");

        if (!$product) {
            Response::badRequest("Невалидно id на продукт")->send();
        }
        
        if ($product["quantity"] <= 0) {
            Response::badRequest("Няма наличност от този продукт")->send();
        }

        $user = UserService::isAuthenticated();

        if (!$user) {
            CartService::saveItemToSession($product, $data);
            Response::ok($_SESSION[CartService::$cart])->send();
        } else {
            $userId = $user["id"];

            if ($data->cart_product_quantity == 0) {
                CartService::deleteItem($data, $userId);
            } else {
                CartService::saveCartItem($data, $userId, $product);
            }

            $cartItems = CartService::getCartItems($userId, true);
            Response::ok($cartItems)->send();
        }
    }

    public static function getItems()
    {
        $user = UserService::isAuthenticated();

        $cartItems = CartService::getCartItems($user["id"] ?? null, true);
        Response::ok($cartItems)->send();
    }
}
