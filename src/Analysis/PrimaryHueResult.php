<?php

declare(strict_types=1);

namespace Medas\ImageManager\Analysis;

class PrimaryHueResult
{
    public function __construct(
        public float $primaryHue,
        public float $primaryStrength,
        public float $secondaryHue,
        public float $secondaryStrength,
        public float $saturation,
        public float $luminosity,
    )
    {
    }
}
