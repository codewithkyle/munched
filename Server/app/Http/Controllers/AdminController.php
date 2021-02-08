<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

// Services
use App\Services\UserService;
use App\Services\AdminService;

class AdminController extends Controller
{
    public function verify(Request $request): JsonResponse
    {
        return $this->buildSuccessResponse();
    }

    public function getUsers(Request $request): JsonResponse
    {
        $adminService = new AdminService();
        $page = $request->input("p", 0);
        $limit = $request->input("limit", 10);
        $users = $adminService->getUsers($page, $limit);
        return $this->buildSuccessResponse($users);
    }
}
