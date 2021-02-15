<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user;

        return $this->buildSuccessResponse($user);
    }

    public function updateEmail(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "email" => "required|email|unique:users|max:255",
        ]);
        if ($validator->fails()) {
            return $this->buildValidationErrorResponse($validator, "Email update form contains errors.");
        }

        $user = $request->user;
        $newEmail = $request->input("email");

        $userService = new UserService($user);
        $userService->createEmailVerification($newEmail);

        return $this->buildSuccessResponse();
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "name" => "required|max:255",
        ]);
        if ($validator->fails()) {
            return $this->buildValidationErrorResponse($validator, "Profile update form contains errors.");
        }

        $user = $request->user;
        $userService = new UserService($user);
        $userService->updateProfile($request->all());

        return $this->buildSuccessResponse();
    }
}
