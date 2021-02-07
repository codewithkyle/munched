<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    public function verify(Request $request): JsonResponse
    {
        return $this->buildSuccessResponse();
    }

    public function getUsers(Request $request): JsonResponse
    {
        return $this->buildSuccessResponse();
    }
}
