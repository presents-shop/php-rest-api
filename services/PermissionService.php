<?php

class PermissionService
{
    public static function create($data)
    {
        if (empty($data->name) || empty($data->label)) {
            Response::badRequest("Не можете да създадете разрешение, без име лейбъл.")->send();
        }

        if (!empty($data->name)) {
            $permission = self::findOne($data->name, "name");

            if ($permission) {
                Response::badRequest("Вече съществува разрешение със зададеното име. Моля, задайте друго име за новото разрешение!")->send();
            }
        }

        $newPermission = [
            "name" => $data->name ?? null,
            "label" => $data->label ?? null,
            "text" => $data->text ?? null,
        ];

        global $database;

        try {
            $database->insert("permissions", $newPermission);
            return self::findOne($database->lastInsertedId());
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function findOne($value, $column = "id", $fields = "*")
    {
        global $database;

        $sql = "SELECT $fields FROM permissions WHERE $column = :$column";
        $params = [":$column" => $value];

        try {
            $permission = $database->getOne($sql, $params);
            return $permission;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function update($id, $data)
    {
        global $database;

        $role = self::findOne($id);

        if (!$role) {
            Response::badRequest("Това разрешение не съществува.")->send();
        }

        if (empty($data->name) || empty($data->label)) {
            Response::badRequest("Не можете да създадете разрешение, без име лейбъл.")->send();
        }

        $permissionByName = self::findOne($data->name, "name");

        if ($permissionByName && $permissionByName["id"] != $data->id) {
            Response::badRequest("Вече съществува друго разрешение със зададеното име.")->send();
        }

        $updatedPermission = [
            "name" => $data->name,
            "label" => $data->label,
            "text" => $data->text ?? null,
        ];

        try {
            $database->update("permissions", $updatedPermission, "id = $id");
            return self::findOne($id);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function delete($id)
    {
        global $database;

        $permission = self::findOne($id);

        if (!$permission) {
            Response::badRequest("Това разрешение не съществува.")->send();
        }

        try {
            return $database->delete("permissions", "id = $id", []);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function findAll($offset, $limit, $search, $sort)
    {
        global $database;

        $sql = "SELECT * FROM permissions";

        if ($sort == "asc" || $sort == "desc") {
            $sql .= " ORDER BY label $sort";
        }

        if ($sort == "new" || $sort == "old") {
            $method = $sort == "new" ? "desc" : "asc";
            $sql .= " ORDER BY id $method";
        }

        if ($search) {
            $sql .= " WHERE label LIKE '%$search%'";
        }

        if ($limit) {
            $sql .= " LIMIT $limit";
        }

        if ($offset) {
            $sql .= " OFFSET $offset";
        }

        try {
            $permissions = $database->getAll($sql, []);
            return $permissions;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function getItemsLength($search)
    {
        global $database;

        $sql = "SELECT COUNT(*) AS 'length' FROM permissions";

        if ($search) {
            $sql .= " WHERE label LIKE '%$search%'";
        }

        try {
            $data = $database->getOne($sql, []);
            return $data["length"] ?? 0;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
}
