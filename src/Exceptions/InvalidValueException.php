<?php

declare(strict_types=1);

namespace Medas\ImageManager\Exceptions;

use Medas\Core\Exceptions\BaseException;

class InvalidValueException extends BaseException
{
    public function __construct(mixed $value, string $type)
    {
        parent::__construct($value, $type);
    }

    public function pattern(): string
    {
        return '%s is not a valid value for %s';
    }
}
