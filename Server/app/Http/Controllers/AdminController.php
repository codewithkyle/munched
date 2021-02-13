<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

// Services
use App\Services\UserService;
use App\Services\AdminService;

// Mdoels
use App\Models\User;

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
        $data = $adminService->getUsers($page, $limit);
        return $this->buildSuccessResponse($data);
    }

    public function banUser(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "uid" => "required",
        ]);
        if ($validator->fails()){
            return $this->buildValidationErrorResponse($validator, "Request is missing uid parameter.");
        }

        $uid = $request->input("uid");
        $user = User::where("uid", $uid)->first();
        if (!empty($user)){
            $userService = new UserService($user);
            $userService->suspend();
            return $this->buildSuccessResponse();
        } else {
            return $this->buildErrorResponse("Failed to find user to suspend.");
        }
    }

    public function unbanUser(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "uid" => "required",
        ]);
        if ($validator->fails()){
            return $this->buildValidationErrorResponse($validator, "Request is missing uid parameter.");
        }

        $uid = $request->input("uid");
        $user = User::where("uid", $uid)->first();
        if (!empty($user)){
            $userService = new UserService($user);
            $userService->unsuspend();
            return $this->buildSuccessResponse();
        } else {
            return $this->buildErrorResponse("Failed to find user to unsuspend.");
        }
    }

    public function activateUser(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "uid" => "required",
        ]);
        if ($validator->fails()){
            return $this->buildValidationErrorResponse($validator, "Request is missing uid parameter.");
        }

        $uid = $request->input("uid");
        $user = User::where("uid", $uid)->first();
        if (!empty($user)){
            $userService = new UserService($user);
            $userService->activate();
            return $this->buildSuccessResponse();
        } else {
            return $this->buildErrorResponse("Failed to find user to unsuspend.");
        }
    }

    public function sendActivationEmail(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "uid" => "required",
        ]);
        if ($validator->fails()){
            return $this->buildValidationErrorResponse($validator, "Request is missing uid parameter.");
        }

        $uid = $request->input("uid");
        $user = User::where("uid", $uid)->first();
        if (!empty($user)){
            $userService = new UserService($user);
            $userService->resendVerificationEmail();
            return $this->buildSuccessResponse();
        } else {
            return $this->buildErrorResponse("Failed to find user to unsuspend.");
        }
    }

    public function revokeAdminStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "uid" => "required",
        ]);
        if ($validator->fails()){
            return $this->buildValidationErrorResponse($validator, "Request is missing uid parameter.");
        }

        $uid = $request->input("uid");
        $user = User::where("uid", $uid)->first();
        if (!empty($user)){
            $userService = new UserService($user);
            $userService->revokeAdminStatus();
            return $this->buildSuccessResponse();
        } else {
            return $this->buildErrorResponse("Failed to find user to unsuspend.");
        }
    }

    public function grantAdminStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "uid" => "required",
        ]);
        if ($validator->fails()){
            return $this->buildValidationErrorResponse($validator, "Request is missing uid parameter.");
        }

        $uid = $request->input("uid");
        $user = User::where("uid", $uid)->first();
        if (!empty($user)){
            $userService = new UserService($user);
            $userService->grantAdminStatus();
            return $this->buildSuccessResponse();
        } else {
            return $this->buildErrorResponse("Failed to find user to unsuspend.");
        }
    }
}
