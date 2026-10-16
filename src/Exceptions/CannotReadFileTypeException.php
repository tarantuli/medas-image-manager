<?php

declare(strict_types=1);

namespace Medas\ImageManager\Exceptions;

use Medas\Core\Exceptions\BaseException;

class CannotReadFileTypeException extends BaseException
{
    /**
     * @param int|false $imageType An IMAGETYPE_* constant, or false when exif_imagetype() couldn't recognise the file
     *                             at all (e.g. text, or content too short to have a header)
     */
    public function __construct(string $fileName, int|false $imageType)
    {
        parent::__construct(
            $fileName,
            $imageType === false ? 'unknown' : image_type_to_mime_type($imageType)
        );
    }

    public function pattern(): string
    {
        return 'Cannot read file %s with type %s';
    }
}
