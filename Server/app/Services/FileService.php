<?php

namespace App\Services;

use Log;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Illuminate\Support\Facades\Hash;

use App\Models\File;
use App\Facades\File as FileHelper;

class FileService
{
    public static function SaveFile(UploadedFile $uploadedFile, string $uid = null): void
    {
        $file = null;
        $key = null;
        if (!is_null($uid)){
            $file = File::where("uid", $uid)->first();
            if (!empty($file)){
                $key = $file->key;
            }
        }
        if (is_null($file) || is_null($key)){
            $uid = Uuid::uuid4()->toString();
            $key = Hash::make(uid);
            $file = File::create([
                "uid" => $uid,
                "key" => $key,
            ]);
        }
        FileHelper::Put($key, $uploadedFile->getRelativePathname());
        if ($file->deleted){
            $file->deleted = false;
            $file->save();
        }
    }

    public static function DeleteFile(string $uid): void
    {
        $file = File::where("uid", $uid)->first();
        if (!empty($file))
        {
            FileHelper::Delete($file->key);
            $file->deleted = true;
            $file->save();
        }
    }

    public static function GetKey(string $uid): string
    {
        $file = File::where("uid", $uid)->first();
        $key = $file->key ?? "";
        return $key;
    }
}
