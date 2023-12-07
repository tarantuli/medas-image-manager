<?php

declare(strict_types=1);

namespace Medas\ImageManager\Analysis;

use Medas\Core\Attributes\Service;
use Medas\ImageManager\{Color, ColorManager, Image};

#[Service]
readonly class PrimaryHueFinder
{
    public function __construct(
        private ColorManager $colorManager,
    )
    {
    }

    public function find(Image $image): PrimaryHueResult
    {
        $hwidth = $image->width() / 2;
        $hheight = $image->height() / 2;

        // Result variables
        $hues = [];
        $saturationSum = 0.0;
        $saturationDivider = 0.0;
        $luminositySum = 0.0;
        $luminosityDivider = 0.0;
        $doStagger = false;

        for ($radius = 0; $radius < 100; $radius += 20) {
            $doStagger = !$doStagger;

            for ($phi = 0; $phi < 360; $phi += 30) {
                // Determine coordinates
                $x = (int) ($hwidth + $hwidth * sin(2 * M_PI * ($phi + ($doStagger ? 15 : 0)) / 360) * $radius / 100);
                $y = (int) ($hheight + $hheight * cos(2 * M_PI * ($phi + ($doStagger ? 15 : 0)) / 360) * $radius / 100);

                // Get color information of this pixel
                $colorInfo = $image->colorAt($x, $y);

                $color = new Color(
                    $colorInfo['red'] / 255,
                    $colorInfo['green'] / 255,
                    $colorInfo['blue'] / 255,
                    $colorInfo['alpha']
                );

                [$hue, $saturation, $luminosity] = $this->colorManager->getHsl($color);

                // Determine weight
                $weight = $saturation * (100 - $radius);
                $hues[] = [cos(2 * M_PI * $hue), sin(2 * M_PI * $hue), $weight];

                // Saturation
                $saturationSum += $saturation;
                $saturationDivider += 1;

                // Luminosity
                $luminositySum += $luminosity;
                $luminosityDivider += 1;

                if ($radius == 0) {
                    break;
                }
            }
        }

        // Primary hue
        $hueXSum = 0.0;
        $hueYSum = 0.0;
        $hueDivider = 0.0;

        foreach ($hues as $set) {
            [$hueX, $hueY, $weight] = $set;
            $hueXSum += $hueX * $weight;
            $hueYSum += $hueY * $weight;
            $hueDivider += $weight;
        }

        [$primaryHue, $primaryStrength] = $this->determineHueAndStrength(
            $hueDivider,
            $hueXSum,
            $hueYSum
        );

        // Secondary hue
        $hueXSum = 0.0;
        $hueYSum = 0.0;
        $hueDivider = 0.0;

        foreach ($hues as $set) {
            [$hueX, $hueY, $weight] = $set;
            $hue = atan2($hueY, $hueX);

            if ($hue < 0) {
                $hue += 2 * M_PI;
            }

            $distance = abs($hue - $primaryHue) / M_PI;
            $weight *= $distance * $distance;
            $hueXSum += $hueX * $weight;
            $hueYSum += $hueY * $weight;
            $hueDivider += $weight;
        }

        [$secondaryHue, $secondaryStrength] = $this->determineHueAndStrength(
            $hueDivider,
            $hueXSum,
            $hueYSum
        );

        $aveSat = $saturationSum / $saturationDivider;
        $aveLum = $luminositySum / $luminosityDivider;

        return new PrimaryHueResult(
            primaryHue: 360 * $primaryHue / 2 / M_PI,
            primaryStrength: $primaryStrength,
            secondaryHue: 360 * $secondaryHue / 2 / M_PI,
            secondaryStrength: $secondaryStrength,
            saturation: $aveSat,
            luminosity: $aveLum,
        );
    }

    private function determineHueAndStrength(mixed $hueDivider, float|int $hueXSum, float|int $hueYSum): array
    {
        if ($hueDivider == 0) {
            $hueX = 1;
            $hueY = 0;
        }
        else {
            $hueX = $hueXSum / $hueDivider;
            $hueY = $hueYSum / $hueDivider;
        }

        $primaryHue = atan2($hueY, $hueX);

        if ($primaryHue < 0) {
            $primaryHue += 2 * M_PI;
        }

        $primaryStrength = sqrt($hueX * $hueX + $hueY * $hueY);

        return [$primaryHue, $primaryStrength];
    }
}
