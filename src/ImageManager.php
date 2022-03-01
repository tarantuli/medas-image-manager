<?php

declare(strict_types=1);

namespace Medas\ImageManager;

use Medas\FileSystem\TemporaryFiles;
use Medas\ServiceManager\Attributes\Service;

#[Service]
class ImageManager
{
    public function __construct(
        private TemporaryFiles $temporaryFiles
    )
    {
    }

    public function fromContent(string $content): Image
    {
        return $this->fromFile($this->temporaryFiles->create($content));
    }

    public function fromFile(string $fileName): Image
    {
        $resource = $this->getResource($fileName);

        if ($resource === null) {
            throw new Exceptions\CannotReadFileException($fileName);
        }
        return new Image($resource);
    }

    private function getResource(string $fileName): \GdImage|null
    {
        $imageType = exif_imagetype($fileName);

        switch ($imageType) {
            case IMAGETYPE_GIF:
                return imagecreatefromgif($fileName) ?: null;

            case IMAGETYPE_JPEG:
                return imagecreatefromjpeg($fileName) ?: null;

            case IMAGETYPE_PNG:
                return imagecreatefrompng($fileName) ?: null;

            case IMAGETYPE_WBMP:
                return imagecreatefromwbmp($fileName) ?: null;

            case IMAGETYPE_WEBP:
                return imagecreatefromwebp($fileName) ?: null;

            case IMAGETYPE_BMP:
                throw new Exceptions\CannotReadBmpFileException($fileName);

            default:
                throw new Exceptions\CannotReadFileTypeException($fileName, $imageType);
        }
    }

    public function create(int $width, int $height): Image
    {
        $resource = imagecreatetruecolor($width, $height);

        // Turn alpha blending off, and do tell the resource to save alpha information
        imagealphablending($resource, false);
        imagesavealpha($resource, true);

        return new Image($resource);
    }
}
