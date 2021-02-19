<?php

namespace App\Services;

use Log;

use App\Models\User;

class IngestService
{
    public function getAllUsers()
    {
        $output = [];
        $users = User::get();
        foreach ($users as $user) {
            $output[] = [
                "Name" => $user->name,
                "Email" => $user->email,
                "Uid" => $user->uid,
                "Suspended" => boolval($user->suspended),
                "Verified" => boolval($user->verified),
                "Admin" => boolval($user->admin),
            ];
        }
        return $output;
    }
}
