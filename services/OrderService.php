<?php

use Jchook\Uuid;

class OrderService
{
    public static function create($data)
    {
        global $database;

        $error = self::validateOrder($data);
        if (is_string($error)) {
            Response::badRequest($error)->send();
            return;
        }

        $newOrder = [
            "id" => Uuid::v4(),
            "customer_id" => $data->customer_id,
            "order_status" => $data->order_status ?? "processing",
            "total_amount" => $data->total_amount,
            "shipping_options" => json_encode($data->shipping_options ?? []),
            "payment_method" => $data->payment_method,
            "shipping_method" => $data->shipping_method,
            "shipping_cost" => $data->shipping_cost ?? null,
            "product_list" => json_encode($data->product_list ?? []),
            "customer_notes" => $data->customer_notes ?? null,
            "estimated_delivery_date" => $data->estimated_delivery_date ?? "",
            "customer_email" => $data->customer_email,
            "customer_phone" => $data->customer_phone,
            "delivery_instructions" => $data->delivery_instructions ?? null,
            "gift_wrap" => $data->gift_wrap,
            "gift_message" => $data->gift_message ?? null,
            "order_comments" => json_encode($data->order_comments ?? []),
            "risk_flat" => $data->risk_flat ?? 0,
        ];

        $database->insert("orders", $newOrder);

        return self::getItem($newOrder["id"]);
    }

    private static function validateOrder($data)
    {
        if (!isset($data->total_amount) || !is_numeric($data->total_amount)) {
            return "Total amount is required and must be a valid number.";
        }

        if (empty($data->payment_method)) {
            return "Payment method is required.";
        }

        if (empty($data->shipping_method)) {
            return "Shipping method is required.";
        }

        if (empty($data->product_list)) {
            return "Product list is required and must be a valid JSON.";
        }

        if (empty($data->customer_email) || !filter_var($data->customer_email, FILTER_VALIDATE_EMAIL)) {
            return "Valid customer email is required.";
        }

        if (empty($data->customer_phone)) {
            return "Customer phone number is required.";
        }

        return true;
    }

    public static function calculateTotalAmount($user)
    {
        $cartAndProductItems = CartService::getCartItems($user["id"] ?? null, true);

        // Проверка дали количката е празна
        if (count($cartAndProductItems) == 0) {
            global $database;
            $database->rollBack();
            Response::badRequest("Нямате продукти в кошницата")->send();
            return; // Прекратяване на метода, тъй като количката е празна
        }

        $totalAmount = 0;
        foreach ($cartAndProductItems as $cartAndProductItem) {
            // Добавяне на сумата за текущия артикул към общата сума
            $totalAmount += floatval($cartAndProductItem["cart_product"]["cart_product_amount"]);
        }

        // Връщане на общата сума на поръчката
        return $totalAmount;
    }

    public static function updateProductsQuantity($user)
    {
        $cartAndProductItems = CartService::getCartItems($user["id"] ?? null, true);

        if (count($cartAndProductItems) <= 0) {
            global $database;
            $database->rollBack();
            Response::badRequest("Нямате продукти в кошницата")->send();
            return;
        }

        foreach ($cartAndProductItems as $cartAndProductItem) {
            $remainingQuantity = intval($cartAndProductItem["product"]["quantity"]) - intval($cartAndProductItem["cart_product"]["cart_product_quantity"]);

            // Проверка дали оставащото количество е валидно
            if ($remainingQuantity < 0) {
                global $database;
                $database->rollBack();
                Response::badRequest("Недостатъчно количество за продукта: " . $cartAndProductItem["product"]["name"])->send();
                return;
            }

            // Обновяване на количеството на продукта
            $productId = $cartAndProductItem["cart_product"]["cart_product_id"];
            ProductService::updateQuantity($productId, $remainingQuantity);
        }
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

            if ($order["product_list"]) {
                $order["product_list"] = json_decode($order["product_list"]);
            }
            if ($order["order_comments"]) {
                $order["order_comments"] = json_decode($order["order_comments"]);
            }
            if ($order["shipping_options"]) {
                $order["shipping_options"] = json_decode($order["shipping_options"]);
            }

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

    public static function sendOrderEmail($order, $options)
    {
        $html = "";

        foreach ($order["product_list"] as $item) {
            $html .= "<tr>\n";
            $html .= "<td>" . $item["cart_product_name"] . "</td>\n";
            $html .= "<td>" . $item["cart_product_quantity"] . "</td>\n";
            $html .= "<td>" . $item["cart_product_price"] . "</td>\n";
            $html .= "<td>" . $item["cart_product_amount"] . "</td>\n";
            $html .= "</tr>\n";
        }

        $variables = [
            "rows" => $html,
            "amount" => $order["total_amount"],
            "message" => $options["message"] ?? null
        ];

        $html = file_get_contents("email-templates/completed-order.html");
        $processedHtml = HTMLTemplateProcessor::replaceVariables($html, $variables);

        $mail = new Mail($order["customer_email"], $options["title"], $processedHtml);
        $mail->send();
    }
}