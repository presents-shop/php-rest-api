<?php

use Jchook\Uuid;

class CartService
{
    public static $cart = "cart";

    public static function saveItemToSession($product, $data)
    {
        function findIndexByProductId($product_id, $cart = [])
        {
            foreach ($cart as $index => $item) {
                if ($item["cart_product_id"] == $product_id) {
                    return $index;
                }
            }
            return -1;
        }

        function removeProductFromCart(&$cart, $product_id)
        {
            $index = findIndexByProductId($product_id, $cart);
            if ($index !== -1) {
                array_splice($cart, $index, 1);
            }
        }

        if ($data->cart_product_quantity == 0) {
            removeProductFromCart($_SESSION[self::$cart], $data->cart_product_id);
            Response::ok($_SESSION[self::$cart])->send();
            return;
        }

        if ($product["quantity"] < $data->cart_product_quantity) {
            $data->cart_product_quantity = $product["quantity"];
        }

        $price = floatval($product["selling_price"] ?? null) ? floatval($product["selling_price"]) : floatval($product["original_price"]);

        if (!in_array($data->cart_product_id, array_column($_SESSION[self::$cart] ?? [], "cart_product_id"))) {
            // Ако продуктът не съществува в количката, го добавяме
            $_SESSION[self::$cart][] = [
                "cart_product_id" => $data->cart_product_id,
                "cart_product_name" => $product["name"],
                "cart_product_price" => floatval($price),
                "cart_product_quantity" => intval($data->cart_product_quantity),
                "cart_product_amount" => floatval($price) * intval($data->cart_product_quantity)
            ];
        } else {
            // Ако продуктът съществува, го актуализираме
            foreach ($_SESSION[self::$cart] as &$item) {
                if ($item["cart_product_id"] == $data->cart_product_id) {
                    $item["cart_product_quantity"] = intval($data->cart_product_quantity);
                    $item["cart_product_price"] = floatval($price);
                    $item["cart_product_amount"] = floatval($price) * intval($data->cart_product_quantity);
                    break;
                }
            }
            unset($item); // Добра практика за предотвратяване на нежелани странични ефекти с препратки
        }
    }

    public static function getCartItems($userId, $populate_product = false)
    {
        // Проверка дали потребителят е автентифициран
        if ($userId) {
            global $database;

            $params = [":user_id" => $userId];
            // Извличане на артикулите в количката от базата данни
            $cartItems = $database->getAll("SELECT * FROM cart WHERE cart_user_id = :user_id", $params);
        } else {
            // Извличане на артикулите от сесията, ако потребителят не е автентифициран
            $cartItems = $_SESSION[CartService::$cart] ?? []; // Включена е проверка за наличието на сесията
        }

        // Попълване на информацията за продукта, ако е указано
        if ($populate_product) {
            foreach ($cartItems as &$cartItem) {
                $product = ProductService::findOne($cartItem["cart_product_id"]);

                // Проверка дали продуктът съществува в базата данни
                if ($product) {
                    $cartItem = [
                        "cart_product" => $cartItem,
                        "product" => $product
                    ];
                } else {
                    // Обработка на случай, при който продуктът не е намерен
                    $cartItem["product"] = null;
                }
            }
        }

        // Връщане на артикулите от количката
        return $cartItems;
    }

    public static function saveCartItem($data, $userId, $product)
    {
        global $database;

        // Проверка дали продуктът има налично количество
        if (isset($product["quantity"]) && $product["quantity"] < $data->cart_product_quantity) {
            $data->cart_product_quantity = $product["quantity"];
        }

        // Определяне на цена, като се вземе предвид промоция или стандартна цена
        $price = isset($product["selling_price"]) && $product["selling_price"] ? $product["selling_price"] : $product["original_price"];

        $cartItem = [
            "cart_user_id" => $userId,
            "cart_product_name" => $product["name"] ?? '',
            "cart_product_id" => $data->cart_product_id,
            "cart_product_price" => floatval($price),
            "cart_product_quantity" => intval($data->cart_product_quantity),
            "cart_product_amount" => floatval($price) * intval($data->cart_product_quantity)
        ];

        // Проверка дали продуктът вече съществува в количката
        $existingCartItem = $database->getOne(
            "SELECT * FROM cart WHERE cart_user_id = :cart_user_id AND cart_product_id = :cart_product_id",
            [":cart_user_id" => $userId, ":cart_product_id" => $data->cart_product_id]
        );

        if (!$existingCartItem) {
            // Генериране на уникално ID за новия артикул в количката
            $cartItem["id"] = Uuid::v4();
            $database->insert("cart", $cartItem);
        } else {
            // Актуализиране на съществуващия артикул в количката
            unset($cartItem["cart_user_id"]);
            $productId = $data->cart_product_id;
            $database->update("cart", $cartItem, "cart_user_id = '$userId' AND cart_product_id = '$productId'");
        }
    }

    public static function deleteItem($data, $userId)
    {
        global $database;

        $params = [":cart_user_id" => $userId, ":cart_product_id" => $data->cart_product_id];
        $database->delete("cart", "cart_user_id = :cart_user_id AND cart_product_id = :cart_product_id", $params);
    }

    public static function deleteItemsByUser($userId)
    {
        global $database;

        $params = [":cart_user_id" => $userId];
        $database->delete("cart", "cart_user_id = :cart_user_id", $params);
    }
}