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
        // Checked first: exif_imagetype() can't tell a missing file from one that isn't an image
        if (!is_file($fileName) || !is_readable($fileName)) {
            throw new Exceptions\CannotReadFileException($fileName);
        }

        $resource = $this->getResource($fileName);

        if ($resource === null) {
            throw new Exceptions\CannotReadFileException($fileName);
        }

        return new Image($resource);
    }

    private function getResource(string $fileName): \GdImage|null
    {
        // exif_imagetype emits a notice when the file isn't an image (or is too
        // short to sniff its header); suppress it - a non-image is a valid outcome
        // here, handled by the match's default throw below. It then returns false,
        // which the exception reports as an unknown type.
        $imageType = @exif_imagetype($fileName);

        // GD emits warnings for a file with a valid header but corrupt or truncated data;
        // suppress them too, so the failure (false) becomes CannotReadFileException in
        // fromFile() instead of a warning, or an ErrorException under a strict error policy
        return match ($imageType) {
            IMAGETYPE_GIF => @imagecreatefromgif($fileName) ?: null,
            IMAGETYPE_JPEG => @imagecreatefromjpeg($fileName) ?: null,
            IMAGETYPE_PNG => @imagecreatefrompng($fileName) ?: null,
            IMAGETYPE_WBMP => @imagecreatefromwbmp($fileName) ?: null,
            IMAGETYPE_WEBP => @imagecreatefromwebp($fileName) ?: null,
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

        // Turn alpha blending off and save alpha channel information
        imagealphablending($resource, false);

        imagesavealpha($resource, true);

        return new Image($resource);
    }
}
