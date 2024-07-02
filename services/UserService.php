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

        $options = json_encode([
            "first_name" => $data->first_name,
            "last_name" => $data->last_name
        ]);

        $id = Uuid::v4();
        $token = TokenService::generateToken();

        $newUser = [
            "id" => $id,
            "email" => $data->email,
            "phone" => $data->phone,
            "password" => $passwordHash,
            "options" => $options,
            "token" => $token
        ];

        global $database;

        try {
            $database->insert("users", $newUser);

            $user = self::findOne($id);
            unset($user["token"]);
            
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

    public static function findAll()
    {
        global $database;

        try {
            $users = $database->getAll("SELECT * FROM users");

            foreach($users as &$user) {
                unset($user["password"]);
            }

            return $users;
        } catch (Exception $ex) {
            Response::badRequest("Find all users error: ".$ex->getMessage())->send();
        }
    }

    public static function generateToken($length = 32)
    {
        return bin2hex(random_bytes($length));
    }
}