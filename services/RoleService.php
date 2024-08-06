<?php

class RoleService
{
    public static function create($data)
    {
        if (empty($data->name) || empty($data->label)) {
            Response::badRequest("Не можете да създадете роля, без име лейбъл.")->send();
        }

        if (!empty($data->name)) {
            $role = self::findOne($data->name, "name");

            if ($role) {
                Response::badRequest("Вече съществува роля със зададеното име. Моля, задайте друго име за новата роля!")->send();
            }
        }

        $permissionIds = json_encode($data->permission_ids ?? []);

        $newRole = [
            "name" => $data->name ?? null,
            "label" => $data->label ?? null,
            "text" => $data->text ?? null,
            "permission_ids" => $permissionIds,
        ];

        global $database;

        try {
            $database->insert("roles", $newRole);
            return self::findOne($database->lastInsertedId());
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function findOne($value, $column = "id", $fields = "*")
    {
        global $database;

        $sql = "SELECT $fields FROM roles WHERE $column = :$column";
        $params = [":$column" => $value];

        try {
            $role = $database->getOne($sql, $params);

            if (!empty($role["permission_ids"])) {
                $role["permission_ids"] = json_decode($role["permission_ids"]);

                foreach($role["permission_ids"] as &$permissionId) {
                    $role["permissions"][] = PermissionService::findOne($permissionId);
                }
            }

            return $role;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function update($id, $data)
    {
        global $database;

        $role = self::findOne($id);

        if (!$role) {
            Response::badRequest("Тази роля не съществува.")->send();
        }

        if (empty($data->name) || empty($data->label)) {
            Response::badRequest("Не можете да създадете роля, без име лейбъл.")->send();
        }

        $categoryByName = self::findOne($data->name, "name");

        if ($categoryByName && $categoryByName["id"] != $data->id) {
            Response::badRequest("Вече съществува друга роля с зададеното име.")->send();
        }

        $permissionIds = json_encode($data->permission_ids ?? []);

        $updatedRole = [
            "name" => $data->name,
            "label" => $data->label,
            "text" => $data->text ?? null,
            "permission_ids" => $permissionIds,
        ];

        try {
            $database->update("roles", $updatedRole, "id = $id");
            return self::findOne($id);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function delete($id)
    {
        global $database;

        $role = self::findOne($id);

        if (!$role) {
            Response::badRequest("Тази роля не съществува.")->send();
        }

        try {
            return $database->delete("roles", "id = $id", []);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function findAll($offset, $limit, $search, $sort)
    {
        global $database;

        $sql = "SELECT * FROM roles";

        if ($search) {
            $sql .= " WHERE name LIKE '%$search%' OR label LIKE '%$search%'";
        }

        if ($sort == "asc" || $sort == "desc") {
            $sql .= " ORDER BY label $sort";
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
            $roles = $database->getAll($sql, []);
            return $roles;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function getItemsLength($search)
    {
        global $database;

        $sql = "SELECT COUNT(*) AS 'length' FROM roles";

        if ($search) {
            $sql .= " WHERE name LIKE '%$search%' OR label LIKE '%$search%'";
        }

        try {
            $data = $database->getOne($sql, []);
            return $data["length"] ?? 0;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
}
