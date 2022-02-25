<?php

declare(strict_types=1);

namespace Medas\ImageManager;

class Color
{
    public function __construct(
        public float $blue = 0.0,
        public float $green = 0.0,
        public float $opacity = 1.0,
        public float $red = 0.0,
    )
    {
    }
}
