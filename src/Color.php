<?php

declare(strict_types=1);

namespace Medas\ImageManager;

class Color
{
    public string $hexString;

    public function __construct(
        public float $red = 0.0,
        public float $green = 0.0,
        public float $blue = 0.0,
        public float $opacity = 1.0,
    )
    {
        $this->hexString = str_pad(dechex((int) round(255 * $this->red)), 2, '0', STR_PAD_LEFT)
            . str_pad(dechex((int) round(255 * $this->green)), 2, '0', STR_PAD_LEFT)
            . str_pad(dechex((int) round(255 * $this->blue)), 2, '0', STR_PAD_LEFT)
            . str_pad(dechex((int) round(255 * $this->opacity)), 2, '0', STR_PAD_LEFT);
    }
}
