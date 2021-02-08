<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Validator;

class Controller extends BaseController
{
    protected function buildValidationErrorResponse(Validator $validator, string $error = "Something went wrong on the server."): JsonResponse
    {
        $errors = [];
        foreach ($validator->errors()->all() as $err) {
            $errors[] = $err;
        }
        return response()->json([
            "success" => false,
            "data" => $errors,
            "error" => $error,
        ]);
    }

    protected function buildErrorResponse(string $error = "Something went wrong on the server.", $data = null): JsonResponse
    {
        return response()->json([
            "success" => false,
            "data" => $data,
            "error" => $error,
        ]);
    }

    protected function buildSuccessResponse($data = null): JsonResponse
    {
        return response()->json([
            "success" => true,
            "data" => $data,
            "error" => null,
        ]);
    }
}
