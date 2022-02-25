<?php

declare(strict_types=1);

namespace Medas\ImageManager\Exceptions;

use Medas\Core\Exceptions\BaseException;

class CannotReadFileTypeException extends BaseException
{
    public function __construct(string $fileName, int $imageType)
    {
        parent::__construct($fileName, image_type_to_mime_type($imageType));
    }

    public function pattern(): string
    {
        return 'Cannot read file %s with type %s';
    }
}
