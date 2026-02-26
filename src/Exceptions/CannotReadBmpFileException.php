<?php

declare(strict_types=1);

namespace Medas\ImageManager\Exceptions;

use Medas\Core\Exceptions\BaseException;

class CannotReadBmpFileException extends BaseException
{
    public function __construct(string $fileName)
    {
        parent::__construct($fileName);
    }

    public function pattern(): string
    {
        return 'Cannot read BMP file %s';
    }
}
