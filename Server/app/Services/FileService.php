<?php

namespace App\Services;

use Log;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Illuminate\Support\Facades\Crypt;

use App\Models\File;
use App\Facades\File as FileHelper;

class FileService
{
    public function saveFile(UploadedFile $uploadedFile, int $userId, string $uid = null): void
    {
        $file = null;
        $key = null;
        if (!is_null($uid)) {
            $file = File::where("uid", $uid)->first();
            if (empty($file)) {
                throw new ErrorException("File does not exist.");
            } else if ($file->userId !== $userId) {
                throw new ErrorException("You do not have permission to update the file.");
            } else {
                $key = $file->key;
            }
        }
        if (is_null($key)) {
            $file = self::CreateFile($userId);
            $key = $file->key;
        }
        FileHelper::Put($key, $uploadedFile->getRelativePathname());
        if ($file->deleted) {
            $file->deleted = false;
            $file->save();
        }
    }

    public function deleteFile(string $uid, int $userId): void
    {
        $file = File::where("uid", $uid)->first();
        if (empty($file)) {
            throw new ErrorException("File does not exist.");
        } else if ($file->userId !== $userId) {
            throw new ErrorException("You do not have permission to delete the file.");
        } else {
            FileHelper::Delete($file->key);
            $file->deleted = true;
            $file->save();
        }
    }

    public function getKey(string $uid): string
    {
        $file = File::where("uid", $uid)->first();
        if (empty($file)){
            throw new ErrorException("File does not exist.");
        } else {
            return $file->key;
        }
    }

    public function getPrivateKey(string $uid, int $userId): string
    {
        $file = File::where("uid", $uid)->first();
        if (empty($file)){
            throw new ErrorException("File does not exist.");
        } else if ($file->userId !== $userId) {
            throw new ErrorException("You do not have permission to view the file.");
        } else {
            return $file->key;
        }
    }

    private function createFile(int $userId): File
    {
        $uid = Uuid::uuid4()->toString();
        $key = Crypt::encrypt(uid);
        $file = File::create([
            "uid" => $uid,
            "key" => $key,
            "userId" => $userId,
        ]);
        return $file;
    }
}
