<?php

declare(strict_types=1);

namespace Medas\ImageManager\Exceptions;

use Medas\Core\Exceptions\BaseException;

class FailedToCreateGdImageResource extends BaseException
{
    public function __construct(int $width, int $height)
    {
        parent::__construct($width, $height);
    }

    public function pattern(): string
    {
        return 'Failed to create GD image resource with width %s and height %s';
    }
}
