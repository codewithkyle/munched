<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use App\Services\FileService;
use App\Services\ImageService;
use App\Facades\File;

class FileController extends Controller
{
    public function getImage(string $uid, Request $request)
    {
        try {
            $imageService = new ImageService();
            $response = $imageService->getImage($uid, $request->user->id, $request->all());
            return response($response["Body"], 200, [
                "Content-Type" => $response["ContentType"],
            ]);
        } catch (Exception $e) {
            return response($e->getMessage(), $e->statusCode);
        }
    }

    public function getFile(string $uid, Request $request)
    {
        try {
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
