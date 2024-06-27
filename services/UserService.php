<?php

class UserService
{
    private $errors = [];

    public function register($data)
    {
        $user = self::findOne($data["email"], "email");

        if ($user) {
            $this->errors["dublicate_email"] = "Този имейл вече е използван за регистрация.";
            return false;
        }

        $newUser = [
            "email" => $data["email"],
            "password" => password_hash($data["password"], PASSWORD_DEFAULT),
            "options" => json_encode($data["options"]),
            "token" => self::generateToken()
        ];

        global $database;

        try {
            $database->insert("users", $newUser);
        } catch (Exception $ex) {
            echo "Insert user error: " . $ex->getMessage();
        }

        return $this->findOne($database->lastInsertedId());
    }

    public function login($data)
    {
        $user = self::findOne($data["email"], "email", "*", true);

        
        if (!$user) {
            $this->errors["invalid_credentials"] = "Имейл адрес или парола са невалидни.";
            return false;
        }
        
        if (!password_verify($data["password"], $user["password"])) {
            $this->errors["invalid_credentials"] = "Имейл адрес или парола са невалидни.";
            return false;
        }
        
        $jwt = new JsonWebToken();
        $token = $jwt->generate(["email" => $user["email"]], JSON_WEB_TOKEN_EXPIRLY_IN_SECONDS);

        return $token;
    }

    public function findOne($value, $column = "id", $fields = "*", $withPassword = false)
    {
        global $database;

        $sql = "SELECT $fields FROM users WHERE $column = :$column";
        $params = [":$column" => $value];

        try {
            $user = $database->getOne($sql, $params);

            if (!$withPassword) {
                unset($user["password"]);
            }

            return $user;
        } catch (Exception $ex) {
            echo "Find single user error: " . $ex->getMessage();
        }
    }

    public function findAll()
    {
        global $database;

        try {
            $users = $database->getAll("SELECT * FROM users");

            foreach($users as &$user) {
                unset($user["password"]);
            }

            return $users;
        } catch (Exception $ex) {
            echo "Find all users error: " . $ex->getMessage();
        }
    }

    public static function generateToken($length = 32)
    {
        return bin2hex(random_bytes($length));
    }

    public function getErrors() {
        return $this->errors;
    }
}