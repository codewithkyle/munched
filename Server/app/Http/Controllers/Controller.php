<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    private function encodeData (string $data): string
    {
        return base64_encode($data);
    }

    private function decodeData(string $encodedData): string
    {
        return base64_decode($encodedData);
    }
}
