<?php

namespace App\Services;

use Log;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException as Exception;
use Imagick;
use Illuminate\Support\Facades\Crypt;

use App\Models\Image;
use App\Facades\File;
use App\Models\TransformedImage;

class ImageService
{
    public function deleteImage(string $uid, int $userId): void
    {
        $image = Image::where("uid", $uid)->first();
        if (empty($image)) {
            throw new Exception(404, "Image does not exist.");
        } elseif ($image->userId !== $userId) {
            throw new Exception(401, "You do not have permission to view this image.");
        } else {
            File::Delete($image->key);
            $image->deleted = true;
            $image->save();
            $this->purgeImageTransforms($image->id);
        }
    }

    public function saveImage(UploadedFile $uploadedFile, int $userId, string $uid = null): string
    {
        $image = null;
        $key = null;
        if (!is_null($uid)) {
            $image = Image::where("uid", $uid)->first();
            if (empty($image)) {
                throw new Exception(404, "Image does not exist.");
            } elseif ($image->userId !== $userId) {
                throw new Exception(401, "You do not have permission to view this image.");
            } else {
                $key = $image->key;
            }
        }
        if (is_null($key)) {
            $image = $this->createImage($userId, $uploadedFile);
            $key = $image->key;
        }
        File::Put($key, $uploadedFile->getPathname());
        unlink($uploadedFile->getPathname());
        if ($image->deleted) {
            $image->deleted = false;
            $image->save();
        }
        return $image->uid;
    }

    public function getTransformedImage(string $uid, int $userId, array $params)
    {
        $image = Image::where("uid", $uid)->first();
        if (empty($image) || $image->deleted) {
            throw new Exception(404, "Image does not exist.");
        } elseif ($image->private && $image->userId !== $userId) {
            throw new Exception(401, "You do not have permission to view this image.");
        }

        $clientAcceptsWebp = strpos($_SERVER["HTTP_ACCEPT"], "image/webp") !== false;
        $transform = $this->buildTransformSettings($params, $image, $clientAcceptsWebp);
        $token = $this->buildTransformToken($transform);
        $transformedImage = TransformedImage::where([
            "token" => $token,
            "imageId" => $image->id,
        ])->first();

        if (empty($transformedImage)) {
            $file = File::Get($image->key);
            $tempImage = storage_path("images") . "/" . Uuid::uuid4()->toString();
            file_put_contents($tempImage, $file["Body"]);
            $this->transformImage($tempImage, $transform, $params);
            $this->convertImageFormat($tempImage, $transform);
            $this->saveTransformedImage($tempImage, $token, $image->id);
            $file = [
                "Body" => file_get_contents($tempImage),
                "ContentType" => mime_content_type($tempImage),
            ];
            unlink($tempImage);
        } else {
            $file = File::Get($transformedImage->key);
        }

        return $file;
    }

    private function createImage(int $userId, UploadedFile $uploadedFile): Image
    {
        $uid = Uuid::uuid4()->toString();
        $key = Crypt::encrypt($uid);
        $img = new Imagick($uploadedFile->getPathname());
        $image = Image::create([
            "uid" => $uid,
            "key" => $key,
            "userId" => $userId,
            "width" => $img->getImageWidth(),
            "height" => $img->getImageHeight(),
            "contentType" => $uploadedFile->getMimeType(),
        ]);
        return $image;
    }

    private function saveTransformedImage(string $imagePath, string $token, int $imageId): void
    {
        $uid = Uuid::uuid4()->toString();
        $key = Crypt::encrypt($uid);
        File::Put($key, $imagePath);
        TransformedImage::create([
            "key" => $key,
            "imageId" => $imageId,
            "uid" => $uid,
            "token" => $token,
        ]);
    }

    private function convertImageFormat(string $tempImage, array $transform): void
    {
        $img = new Imagick($tempImage);
        switch ($transform["format"]) {
            case "jpeg":
                $img->setImageFormat("jpeg");
                $img->setImageCompressionQuality($transform["quality"]);
                $img->writeImage($tempImage);
                break;
            case "gif":
                $img->setImageFormat("gif");
                $img->setImageCompressionQuality($transform["quality"]);
                $img->writeImage($tempImage);
                break;
            case "png":
                $img->setImageFormat("png");
                $img->setImageCompressionQuality($transform["quality"]);
                $img->writeImage($tempImage);
                break;
            default:
                if (\count(\Imagick::queryFormats("WEBP")) > 0 || file_exists("/usr/bin/cjpeg")) {
                    if (\count(\Imagick::queryFormats("WEBP")) > 0) {
                        $img->setImageFormat("webp");
                        $img->setImageCompressionQuality($transform["quality"]);
                        $img->writeImage($tempImage);
                    } elseif (file_exists("/usr/bin/cwebp")) {
                        $command = escapeshellcmd("/usr/bin/cwebp -q " . $transform["quality"] . " " . $tempImage . " -o " . $tempImage);
                        shell_exec($command);
                    }
                }
                break;
        }
    }

    private function transformImage(string $tempImage, array $transform, array $params): void
    {
        $img = new Imagick($tempImage);
        $img->setImageCompression(Imagick::COMPRESSION_NO);
        $img->setImageCompressionQuality(100);
        $img->setOption("png:compression-level", 9);

        switch ($transform["mode"]) {
            case "fit":
                $img->resizeImage($transform["width"], $transform["height"], Imagick::FILTER_LANCZOS, 0.75);
                $img->writeImage($tempImage);
                break;
            case "letterbox":
                $img->setImageBackgroundColor("#" . $transform["background"]);
                $img->thumbnailImage($transform["width"], $transform["height"], true, true);
                $img->writeImage($tempImage);
                break;
            case "crop":
                $leftPos = floor($img->getImageWidth() * $transform["focusPoint"][0]) - floor($transform["width"] / 2);
                $topPos = floor($img->getImageHeight() * $transform["focusPoint"][1]) - floor($transform["height"] / 2);
                $img->cropImage($transform["width"], $transform["height"], $leftPos, $topPos);
                $img->writeImage($tempImage);
                break;
            default:
                if ((isset($params["w"]) && isset($params["h"])) || (!isset($params["w"]) && !isset($params["h"]))) {
                    if ($transform["width"] < $transform["height"]) {
                        $img->resizeImage($transform["width"], null, Imagick::FILTER_LANCZOS, 0.75);
                    } elseif ($transform["height"] < $transform["width"]) {
                        $img->resizeImage(null, $transform["height"], Imagick::FILTER_LANCZOS, 0.75);
                    } else {
                        $rawWidth = $img->getImageWidth();
                        $rawHeight = $img->getImageHeight();
                        if ($rawWidth < $rawHeight) {
                            $img->resizeImage($transform["width"], null, Imagick::FILTER_LANCZOS, 0.75);
                        } elseif ($rawHeight < $rawWidth) {
                            $img->resizeImage(null, $transform["height"], Imagick::FILTER_LANCZOS, 0.75);
                        } else {
                            $img->resizeImage($transform["width"], $transform["height"], Imagick::FILTER_LANCZOS, 0.75);
                        }
                    }
                } else {
                    if (isset($params["w"])) {
                        $img->resizeImage($transform["width"], null, Imagick::FILTER_LANCZOS, 0.75);
                    } else {
                        $img->resizeImage(null, $transform["height"], Imagick::FILTER_LANCZOS, 0.75);
                    }
                }

                $leftPos = floor($img->getImageWidth() * $transform["focusPoint"][0]) - floor($transform["width"] / 2);
                $topPos = floor($img->getImageHeight() * $transform["focusPoint"][1]) - floor($transform["height"] / 2);
                $img->cropImage($transform["width"], $transform["height"], $leftPos, $topPos);
                $img->writeImage($tempImage);
                break;
        }
    }

    private function buildTransformToken(array $transform): string
    {
        $key =
            $transform["width"] .
            "-" .
            $transform["height"] .
            "-" .
            $transform["focusPoint"][0] .
            "-" .
            $transform["focusPoint"][1] .
            "-" .
            $transform["quality"] .
            "-" .
            $transform["background"] .
            "-" .
            $transform["mode"] .
            "-" .
            $transform["format"];
        return \md5($key);
    }

    private function buildTransformSettings(array $params, Image $image, bool $clientAcceptsWebp): array
    {
        $width = $image->width;
        $height = $image->height;
        $aspectRatioValues = [$width, $height];
        if (isset($params["ar"])) {
            $values = explode(":", $params["ar"]);
            if (count($values) == 2) {
                $aspectRatioValues = [intval($values[0]), intval($values[1])];
            }
        }

        if (isset($params["w"]) && isset($params["h"])) {
            $width = intval($params["w"]);
            $height = intval($params["h"]);
        } elseif (isset($params["w"])) {
            $width = intval($params["w"]);
            $height = ($aspectRatioValues[1] / $aspectRatioValues[0]) * $width;
        } elseif (isset($params["h"])) {
            $height = intval($params["h"]);
            $width = ($aspectRatioValues[0] / $aspectRatioValues[1]) * $height;
        }

        $quality = 80;
        if (isset($params["q"])) {
            $quality = intval($params["q"]);
        }

        $mode = "clip";
        if (isset($params["m"])) {
            $mode = $params["m"];
        }

        $bg = "ffffff";
        if (isset($params["bg"])) {
            $bg = ltrim($params["bg"], "#");
        }

        $focusPoints = [];
        if (isset($params["fp-x"]) && isset($params["fp-y"])) {
            $focusPoints[0] = floatval($params["fp-x"]);
            if ($focusPoints[0] < 0) {
                $focusPoints[0] = 0;
            }
            if ($focusPoints[0] > 1) {
                $focusPoints[0] = 1;
            }

            $focusPoints[1] = floatval($params["fp-y"]);
            if ($focusPoints[1] < 0) {
                $focusPoints[1] = 0;
            }
            if ($focusPoints[1] > 1) {
                $focusPoints[1] = 1;
            }
        } else {
            $focusPoints = [0.5, 0.5];
        }

        $format = "auto";
        if (isset($params["fm"])) {
            switch ($params["fm"]) {
                case "gif":
                    $format = "gif";
                    break;
                case "jpeg":
                    $format = "jpeg";
                    break;
                case "webp":
                    $format = "webp";
                    break;
                case "png":
                    $format = "png";
                    break;
                default:
                    $format = "auto";
                    break;
            }
        }
        if ($format === "auto") {
            if ($clientAcceptsWebp) {
                $format = "webp";
            } else {
                switch ($image->contentType) {
                    case "image/gif":
                        $format = "gif";
                        break;
                    case "image/jpeg":
                        $format = "jpeg";
                        break;
                    default:
                        $format = "png";
                        break;
                }
            }
        }

        $transform = [
            "width" => round($width),
            "height" => round($height),
            "format" => $format,
            "mode" => $mode,
            "quality" => $quality,
            "background" => $bg,
            "focusPoint" => $focusPoints,
        ];
        return $transform;
    }

    private function purgeImageTransforms(int $imageId): void
    {
        $images = TransformedImage::where("imageId", $imageId)->all();
        foreach ($images as $image) {
            File::Delete($image->key);
            $image->delete();
        }
    }
}
