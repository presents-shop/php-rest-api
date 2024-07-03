<?php

use Jchook\Uuid;

class CartService
{
    public static $cart = "cart";

    public static function saveItemToSession($product, $data)
    {
        function findIndexByProductId($cart, $product_id)
        {
            foreach ($cart as $index => $item) {
                if ($item['product_id'] == $product_id) {
                    return $index;
                }
            }
            return -1;
        }

        function removeProductFromCart(&$cart, $product_id)
        {
            $index = findIndexByProductId($cart, $product_id);
            if ($index !== -1) {
                array_splice($cart, $index, 1);
            }
        }

        if ($data->quantity == 0) {
            removeProductFromCart($_SESSION[self::$cart], $data->product_id);
            Response::ok($_SESSION[self::$cart])->send();
        }

        if ($product["quantity"] < $data->quantity) {
            $data->quantity = $product["quantity"];
        }

        $price = $product["selling_price"] ? $product["original_price"] : $product["original_price"];

        if (!in_array($data->product_id, array_column($_SESSION[self::$cart] ?? [], "product_id"))) {
            $_SESSION[self::$cart][] = [
                "product_id" => $data->product_id,
                "price" => $price,
                "quantity" => $data->quantity,
            ];
        } else {
            foreach ($_SESSION[self::$cart] as &$item) {
                if ($item["product_id"] == $data->product_id) {
                    $item["quantity"] = $data->quantity;
                    break;
                }
            }
        }
    }

    public static function getCartItems($userId)
    {
        global $database;

        try {
            $params = [":user_id" => $userId];
            $cartItems = $database->getAll("SELECT * FROM cart WHERE user_id = :user_id", $params);
            return $cartItems;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function saveCartItem($data, $userId, $product)
    {
        global $database;

        if ($product["quantity"] < $data->quantity) {
            $data->quantity = $product["quantity"];
        }

        $price = $product["selling_price"] ? $product["original_price"] : $product["original_price"];

        $cartItem = [
            "user_id" => $userId,
            "product_id" => $data->product_id,
            "price" => $price,
            "quantity" => $data->quantity
        ];

        if (!$database->getOne(
            "SELECT * FROM cart WHERE user_id = :user_id AND product_id = :product_id",
            [":user_id" => $userId, ":product_id" => $data->product_id]
        )) {
            $cartItem["id"] = Uuid::v4();
            $database->insert("cart", $cartItem);
        } else {
            unset($cartItem["user_id"]);
            $productId = $data->product_id;
            $database->update("cart", $cartItem, "user_id = '$userId' AND product_id = $productId");
        }
    }

    public static function deleteItem($data, $userId)
    {
        global $database;

        $params = [":user_id" => $userId, ":product_id" => $data->product_id];
        $database->delete("cart", "user_id = :user_id AND product_id = :product_id", $params);
    }
}
