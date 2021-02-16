<?php

namespace App\Services;

use Log;
use Illuminate\Support\Facades\DB;

// Models
use App\Models\User;

class IngestService
{
    public function getAllUsers()
    {
        $users = User::get();
        return $users;
    }
}
