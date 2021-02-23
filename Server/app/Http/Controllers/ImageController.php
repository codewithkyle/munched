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
        $fileService = new FileService();
        $file = $fileService->getFile($uid);
        return response($file["Body"], 200, [
            "Content-Type" => $file["ContentType"],
        ]);
    }
}
