<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

// Services
use App\Services\IngestService;

class IngestController extends Controller
{
    public function getUsers(Request $request): JsonResponse
    {
        $ingestService = new IngestService();
        $data = $ingestService->getAllUsers();
        return $this->buildSuccessResponse($data);
    }
}
