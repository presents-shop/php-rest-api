<?php

require "vendor/autoload.php";

use Jchook\Uuid;

class UserService
{
    public static function register($data)
    {
        $user = self::findOne($data->email, "email");

        if ($user) {
            Response::badRequest(["dublicate_email" => "Този имейл вече е използван за регистрация."])->send();
        }

        $passwordHash = password_hash($data->password, PASSWORD_DEFAULT);

        $options = json_encode($data->options ?? []);

        $id = Uuid::v4();
        $token = TokenService::generateToken();

        $newUser = [
            "id" => $id,
            "email" => $data->email,
            "phone" => $data->phone ?? null,
            "password" => $passwordHash,
            "options" => $options,
            "token" => $token
        ];

        global $database;

        try {
            $database->insert("users", $newUser);

            $user = self::findOne($id, "id", "*", true);
            return $user;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function login($data)
    {
        $user = self::findOne($data->email, "email", "*", true);
        if (!$user) {
            Response::badRequest(["invalid_credentials" => "Имейл адрес или парола са невалидни."])->send();
        }

        if (!password_verify($data->password, $user["password"])) {
            Response::badRequest(["invalid_credentials" => "Имейл адрес или парола са невалидни."])->send();
        }

        $token = JsonWebToken::generateToken([
            "user_id" => $user["id"],
            "password" => $user["password"]
        ]);

        return $token;
    }

    public static function updateUser($id, $data)
    {
        $user = self::findOne($id);

        if (!$user) {
            Response::badRequest("Този потребител не съществува.")->send();
        }

        $newData = [
            "phone" => $data->phone,
            "options" => json_encode($data->options ?? []),
        ];

        global $database;
        $database->update("users", $newData, "id = '$id'");

        return self::findOne($id);
    }

    public static function findOne($value, $column = "id", $fields = "*", $withPassword = false)
    {
        global $database;

        $sql = "SELECT $fields FROM users WHERE $column = :$column";
        $params = [":$column" => $value];

        try {
            $user = $database->getOne($sql, $params);

            if (!$withPassword && !empty($user["password"])) {
                unset($user["password"]);
            }

            if (!empty($user["options"])) {
                $user["options"] = json_decode($user["options"]);
            }

            return $user;
        } catch (Exception $ex) {
            echo "Find single user error: " . $ex->getMessage();
        }
    }

    public static function findAll($offset, $limit, $search, $sort)
    {
        global $database;

        $sql = "SELECT * FROM users";

        if ($search) {
            $sql .= " WHERE phone LIKE '%$search%' OR email LIKE '%$search%'";
        }

        if ($sort == "asc" || $sort == "desc") {
            $sql .= " ORDER BY email $sort";
        }

        if ($sort == "new" || $sort == "old") {
            $method = $sort == "new" ? "desc" : "asc";
            $sql .= " ORDER BY id $method";
        }

        if ($limit) {
            $sql .= " LIMIT $limit";
        }

        if ($offset) {
            $sql .= " OFFSET $offset";
        }

        try {
            $users = $database->getAll($sql);

            foreach ($users as &$user) {
                unset($user["password"]);
            }

            return $users;
        } catch (Exception $ex) {
            Response::badRequest("Find all users error: " . $ex->getMessage())->send();
        }
    }

    public static function getItemsLength($search)
    {
        global $database;

        $sql = "SELECT COUNT(*) AS 'length' FROM users";

        if ($search) {
            $sql .= " WHERE email LIKE '%$search%' OR phone LIKE '%$search%'";
        }

        try {
            $data = $database->getOne($sql, []);
            return $data["length"] ?? 0;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function forgotPassword(string $email): bool
    {
        $user = self::findOne($email, "email");

        if (!$user) {
            return false;
        }

        $tokenString = TokenService::generateToken();

        self::sendForgotPassword($email, $tokenString);

        return true;
    }

    public static function isAuthenticated(): array|bool
    {
        if (!isset($_SERVER["HTTP_AUTHORIZATION"])) {
            return false;
        }

        $token = explode(" ", $_SERVER["HTTP_AUTHORIZATION"])[1] ?? null;
        // $token = $_SESSION["token"] ?? null;

        try {
            if (!$token) {
                return false;
            }

            $decoded = JsonWebToken::validateToken($token);

            if (!$decoded) {
                return false;
            }

            $user = UserService::findOne($decoded->user_id, "id", "*", true);

            if (!$user) {
                return false;
            }

            if ($decoded->password !== $user["password"]) {
                return false;
            }

            return $user;
        } catch (Exception $ex) {
            return false;
        }
    }

    public static function emailVerify($tokenString = null)
    {
        try {
            if (!TokenService::verifyToken($tokenString)) {
                Response::badRequest(["invalid_token" => "Невалиден линк за потвърждение на профила."])->send();
            }

            global $database;

            $user = self::isAuthenticated();

            if ($user["email_verified"] === 1) {
                Response::badRequest(["error" => "Вече сте потвърдили имейл адреса си"])->send();
            }

            $id = $user["id"];

            if ($user["token"] !== $tokenString) {
                Response::badRequest(["invalid_token" => "Нямате достъп до този ресурс"])->send();
            }

            $data = [
                "email_verified" => 1,
                "token" => null
            ];

            $database->update("users", $data, "id = '$id'");
        } catch (Exception $ex) {
            Response::badRequest(["invalid_token" => "Невалиден линк за потвърждение на профила."])->send();
        }
    }

    public static function generateNewEmailVerifyToken()
    {
        try {
            $user = self::isAuthenticated() ?? null;

            if (!$user) {
                Response::ok(["invalid_credentials" => "Имейл адрес или парола са невалидни"])->send();
            }

            $tokenString = TokenService::generateToken();

            global $database;

            $data = [
                "token" => $tokenString
            ];

            $id = $user["id"];

            $database->update("users", $data, "id = '$id'");
            return self::findOne($id);
        } catch (Exception $ex) {
            Response::badRequest("Update email verify token error: " . $ex->getMessage())->send();
        }
    }

    public static function sendVerifyEmail($email, $token)
    {
        $variables = [
            "host" => WEBSITE_LINK,
            "token_string" => $token
        ];

        $html = file_get_contents("email-templates/email-verify.html");
        $processedHtml = HTMLTemplateProcessor::replaceVariables($html, $variables);

        $mail = new Mail($email, "Успешно направена регистрация!", $processedHtml);
        $mail->send();
    }

    public static function sendForgotPassword($email, $token)
    {
        $variables = [
            "host" => WEBSITE_LINK,
            "reset_link" => $token,
            "email" => $email,
            "website_email" => WEBSITE_EMAIL
        ];

        $html = file_get_contents("email-templates/password-reset.html");
        $processedHtml = HTMLTemplateProcessor::replaceVariables($html, $variables);

        $mail = new Mail($email, "Възстановяване на паролата!", $processedHtml);
        $mail->send();
    }
}
