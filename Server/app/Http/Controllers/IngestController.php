<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException as Exception;

// Services
use App\Services\IngestService;

class IngestController extends Controller
{
    public function getUsers(Request $request)
    {
        try {
            $accepts = $this->validateAcceptHeader($request, ["application/x-ndjson", "application/json"]);
        } catch (Exception $e) {
            return response($e->getMessage(), $e->statusCode);
        }
        switch ($accepts) {
            case "application/x-ndjson":
                $path = storage_path("ndjson/users.ndjson");
                return response()->file($path);
            case "application/json":
                $ingestService = new IngestService();
                $data = $ingestService->getAllUsers();
                return $this->buildSuccessResponse($data);
        }
    }
}
