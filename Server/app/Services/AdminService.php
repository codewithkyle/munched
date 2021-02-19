<?php

namespace App\Services;

use Log;

use App\Models\User;

class AdminService
{
    public function getUsers(int $page, int $limit)
    {
        $offset = $page * $limit;
        $users = User::limit($limit)
            ->offset($offset)
            ->get();
        $total = User::count();
        $output = [
            "users" => $users,
            "total" => $total,
        ];
        return $output;
    }
}
