<?php

class RoleController
{
    public static function saveItem()
    {
        AuthGuard::authenticated();

        $data = getJSONData();

        $id = $data->id ?? null;

        if (!$id) {
            $role = RoleService::create($data);
        } else {
            $role = RoleService::update($id, $data);
        }

        Response::created($role)->send();
    }

    public static function getItem()
    {
        $column = $_GET["column"] ?? null;
        $id = $_GET["id"] ?? null;

        $role = RoleService::findOne($id, $column);

        if (!$role) {
            Response::badRequest("Тази роля не съществува.")->send();
        }

        Response::ok($role)->send();
    }

    public static function getItems()
    {
        $page = $_GET["page"] ?? 1;
        $limit = $_GET["limit"] ?? 5;
        $search = $_GET["search"] ?? null;
        $sort = $_GET["sort"] ?? null;

        $offset = ($page - 1) * $limit;

        $roles = RoleService::findAll($offset, $limit, $search, $sort);
        $length = RoleService::getItemsLength($search);

        Response::ok([
            "items" => $roles,
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

        $result = RoleService::delete($id);

        Response::ok($result)->send();
    }
}