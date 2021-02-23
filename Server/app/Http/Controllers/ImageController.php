<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use App\Services\FileService;
use App\Facades\File;

class ImageController extends Controller
{
    public function getImage(string $uid, Request $request)
    {
        try{
            $fileService = new FileService();
            $response = $fileService->getFile($uid, $request->user->id);
            return response($response["Body"], 200, [
                "Content-Type" => $response["ContentType"],
            ]);
        } catch (Exception $e) {
            return response($e->getMessage(), $e->statusCode);
        }
    }
}
