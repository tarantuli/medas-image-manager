<?php

declare(strict_types=1);

namespace Medas\ImageManager;

use Medas\Core\Attributes\Service;
use Medas\FileSystem\TemporaryFiles;

#[Service]
readonly class ImageManager
{
    public function __construct(
        private TemporaryFiles $temporaryFiles,
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

        return match ($imageType) {
            IMAGETYPE_GIF => imagecreatefromgif($fileName) ?: null,
            IMAGETYPE_JPEG => imagecreatefromjpeg($fileName) ?: null,
            IMAGETYPE_PNG => imagecreatefrompng($fileName) ?: null,
            IMAGETYPE_WBMP => imagecreatefromwbmp($fileName) ?: null,
            IMAGETYPE_WEBP => imagecreatefromwebp($fileName) ?: null,
            IMAGETYPE_BMP => throw new Exceptions\CannotReadBmpFileException($fileName),
            default => throw new Exceptions\CannotReadFileTypeException($fileName, $imageType),
        };
    }

    public function create(int $width, int $height): Image
    {
        $resource = imagecreatetruecolor($width, $height);

        if ($resource === false) {
            throw new Exceptions\FailedToCreateGdImageResource($width, $height);
        }

        // Turn alpha blending off, and save alpha channel information
        imagealphablending($resource, false);

        imagesavealpha($resource, true);

        return new Image($resource);
    }
}
