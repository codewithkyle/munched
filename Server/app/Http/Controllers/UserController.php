<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user;

        return $this->buildSuccessResponse($user);
    }
}
