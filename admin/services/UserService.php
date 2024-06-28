<?php

class UserService
{
    private $errors = [];

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

    public function getErrors() {
        return $this->errors;
    }
}