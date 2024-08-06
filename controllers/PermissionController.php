<?php

class PermissionController
{
    public static function saveItem()
    {
        AuthGuard::authenticated();

        $data = getJSONData();

        $id = $data->id ?? null;

        if (!$id) {
            $permission = PermissionService::create($data);
        } else {
            $permission = PermissionService::update($id, $data);
        }

        Response::created($permission)->send();
    }

    public static function getItem()
    {
        $column = $_GET["column"] ?? null;
        $value = $_GET["value"] ?? null;

        $permission = PermissionService::findOne($value, $column);

        if (!$permission) {
            Response::badRequest("Това разрешение не съществува.")->send();
        }

        Response::ok($permission)->send();
    }

    public static function getItems()
    {
        $page = $_GET["page"] ?? null;
        $limit = $_GET["limit"] ?? null;
        $search = $_GET["search"] ?? null;
        $sort = $_GET["sort"] ?? null;

        if ($page && $limit) $offset = ($page - 1) * $limit;

        $permissions = PermissionService::findAll($offset ?? null, $limit, $search, $sort);
        $length = PermissionService::getItemsLength($search);

        Response::ok([
            "items" => $permissions,
            "length" => $length,
            "params" => [
                "page" => intval($page),
                "limit" => intval($limit),
                "search" => $search,
                "sort" => $sort,
            ]
        ])->send();
    }

    public static function deleteItem()
    {
        AuthGuard::authenticated();

        $id = $_GET["id"];

        $result = PermissionService::delete($id);

        Response::ok($result)->send();
    }
}