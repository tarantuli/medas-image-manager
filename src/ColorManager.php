<?php

declare(strict_types=1);

namespace Medas\ImageManager;

use Medas\Core\Attributes\Service;

#[Service]
readonly class ColorManager
{
    // HSL constants
    public const int HUE = 1;
    public const int LUMINOSITY = 4;
    public const int SATURATION = 2;

    public function getContrast(Color $color1, Color $color2): float
    {
        $rl1 = $this->getRelativeLuminance($color1);
        $rl2 = $this->getRelativeLuminance($color2);

        return (max($rl1, $rl2) + .05) / (min($rl1, $rl2) + .05);
    }

    public function getRelativeLuminance(Color $color): float
    {
        $rmod = $color->red <= .03928
            ? $color->red / 12.92
            : pow(($color->red + .055) / 1.055, 2.4);

        $gmod = $color->green <= .03928
            ? $color->green / 12.92
            : pow(($color->green + .055) / 1.055, 2.4);

        $bmod = $color->blue <= .03928
            ? $color->blue / 12.92
            : pow(($color->blue + .055) / 1.055, 2.4);

        return .2126 * $rmod + .7152 * $gmod + .0722 * $bmod;
    }

    public function fromHtmlString(string $code): Color
    {
        $code = ltrim($code, '#');

        switch (strlen($code)) {
            case 3:
                $opacity = 255;
                $red = hexdec(substr($code, 0, 1) . substr($code, 0, 1));
                $green = hexdec(substr($code, 1, 1) . substr($code, 1, 1));
                $blue = hexdec(substr($code, 2, 1) . substr($code, 2, 1));

                break;

            case 4:
                $opacity = hexdec(substr($code, 0, 1) . substr($code, 0, 1));
                $red = hexdec(substr($code, 1, 1) . substr($code, 1, 1));
                $green = hexdec(substr($code, 2, 1) . substr($code, 2, 1));
                $blue = hexdec(substr($code, 3, 1) . substr($code, 3, 1));

                break;

            case 6:
                $opacity = 255;
                $red = hexdec(substr($code, 0, 2));
                $green = hexdec(substr($code, 2, 2));
                $blue = hexdec(substr($code, 4, 2));

                break;

            case 8:
                $opacity = hexdec(substr($code, 0, 2));
                $red = hexdec(substr($code, 2, 2));
                $green = hexdec(substr($code, 4, 2));
                $blue = hexdec(substr($code, 6, 2));

                break;

            default:
                throw new Exceptions\InvalidValueException($code, 'HTML color code');
        }

        return $this->fromRgba($red / 255, $green / 255, $blue / 255, $opacity / 255);
    }

    public function fromRgba(float $red, float $green, float $blue, float $opacity): Color
    {
        $color = new Color();

        $this->setRed($color, $red);
        $this->setGreen($color, $green);
        $this->setBlue($color, $blue);
        $this->setOpacity($color, $opacity);

        return $color;
    }

    public function setRed(Color $color, $red): void
    {
        if ($red < 0.0 || $red > 1.0) {
            throw new Exceptions\InvalidValueException($red, 'color value');
        }

        $color->red = $red;
    }

    public function setGreen(Color $color, float $green): void
    {
        if ($green < 0.0 || $green > 1.0) {
            throw new Exceptions\InvalidValueException($green, 'color value');
        }

        $color->green = $green;
    }

    public function setBlue(Color $color, float $blue): void
    {
        if ($blue < 0.0 || $blue > 1.0) {
            throw new Exceptions\InvalidValueException($blue, 'blue');
        }

        $color->blue = $blue;
    }

    public function setOpacity(Color $color, float $opacity): void
    {
        if ($opacity < 0.0 || $opacity > 1.0) {
            throw new Exceptions\InvalidValueException($opacity, 'color value');
        }

        $color->opacity = $opacity;
    }

    public function fromRgbaInteger(int $integer): Color
    {
        $opacity = $integer >> 24 & 0xff;
        $red = $integer >> 16 & 0xff;
        $green = $integer >> 8 & 0xff;
        $blue = $integer & 0xff;

        return $this->fromRgba($red / 255, $green / 255, $blue / 255, $opacity / 255);
    }

    public function fromRgbInteger(int $integer): Color
    {
        $red = $integer >> 16 & 0xff;
        $green = $integer >> 8 & 0xff;
        $blue = $integer & 0xff;

        return $this->fromRgb($red / 255, $green / 255, $blue / 255);
    }

    public function fromRgb(float $red, float $green, float $blue): Color
    {
        $color = new Color();

        $this->setRed($color, $red);
        $this->setGreen($color, $green);
        $this->setBlue($color, $blue);

        return $color;
    }

    public function mix(Color $color1, Color $color2, float $ratio = .5): Color
    {
        $complement = 1 - $ratio;
        $red = $color1->red * $complement + $color2->red * $ratio;
        $green = $color1->green * $complement + $color2->green * $ratio;
        $blue = $color1->blue * $complement + $color2->blue * $ratio;
        $opacity = $color1->opacity * $complement + $color2->opacity * $ratio;

        return $this->fromRgba($red, $green, $blue, $opacity);
    }

    public function to100(float $value): int
    {
        return (int) round(100 * $value);
    }

    public function changeBlue(Color $color, float $change): void
    {
        $this->setBlue($color, $color->blue + $change);
    }

    public function changeGreen(Color $color, float $change): void
    {
        $this->setGreen($color, $color->green + $change);
    }

    public function changeHue(Color $color, float $change): void
    {
        $this->setHue($color, $this->getHue($color) + $change);
    }

    public function setHue(Color $color, float $hue): void
    {
        if ($hue < 0.0 || $hue > 1.0) {
            throw new Exceptions\InvalidValueException($hue, 'color value');
        }

        [, $saturation, $luminosity] = $this->getHsl($color);

        $this->setHsl($color, $hue, $saturation, $luminosity);
    }

    public function getHsl(Color $color, int|null $returnValue = null): array|float|int
    {
        $min = min($color->red, $color->green, $color->blue);
        $max = max($color->red, $color->green, $color->blue);
        $luminosity = ($max + $min) / 2;

        if ($returnValue === self::LUMINOSITY) {
            return $luminosity;
        }

        $delta = $max - $min;

        if ($this->isNegligible($delta)) {
            $saturation = 0;

            if ($returnValue === self::SATURATION) {
                return $saturation;
            }

            $hue = 0;
        }
        else {
            if (0.5 > $luminosity) {
                $saturation = $delta / ($max + $min);
            }
            else {
                $saturation = $delta / (2 - $max - $min);
            }

            if ($returnValue === self::SATURATION) {
                return $saturation;
            }

            $deltaR = ((($max - $color->red) / 6) + ($delta / 2)) / $delta;
            $deltaG = ((($max - $color->green) / 6) + ($delta / 2)) / $delta;
            $deltaB = ((($max - $color->blue) / 6) + ($delta / 2)) / $delta;

            if ($this->areComparable($color->red, $max)) {
                $hue = $deltaB - $deltaG;
            }
            elseif ($this->areComparable($color->green, $max)) {
                $hue = (1 / 3) + $deltaR - $deltaB;
            }
            else {
                $hue = (2 / 3) + $deltaG - $deltaR;
            }

            if (0 > $hue) {
                $hue += 1;
            }

            if (1 < $hue) {
                $hue -= 1;
            }
        }

        if ($returnValue === self::HUE) {
            return $hue;
        }

        return [$hue, $saturation, $luminosity];
    }

    public function setHsl(Color $color, float $hue, float $saturation, float $luminosity): void
    {
        if ($this->isNegligible($saturation)) {
            $color->red = $luminosity;
            $color->green = $luminosity;
            $color->blue = $luminosity;

            return;
        }

        if ($luminosity < 0.5) {
            $hvar2 = $luminosity * (1 + $saturation);
        }
        else {
            $hvar2 = ($luminosity + $saturation) - ($luminosity * $saturation);
        }

        $hvar1 = 2 * $luminosity - $hvar2;
        $color->red = self::hueVarsToIntensity($hvar1, $hvar2, $hue + 1 / 3);
        $color->green = self::hueVarsToIntensity($hvar1, $hvar2, $hue);
        $color->blue = self::hueVarsToIntensity($hvar1, $hvar2, $hue - 1 / 3);
    }

    public function isNegligible(float $value): bool
    {
        return $value >= -.001 && $value <= +.001;
    }

    public function getHue(Color $color): float
    {
        return $this->getHsl($color, self::HUE);
    }

    public function changeLuminosity(Color $color, float $change): void
    {
        $this->setLuminosity($color, $this->getLuminosity($color) + $change);
    }

    public function setLuminosity(Color $color, float $luminosity): void
    {
        if ($luminosity < 0.0 || $luminosity > 1.0) {
            throw new Exceptions\InvalidValueException($luminosity, 'color value');
        }

        [$hue, $saturation] = $this->getHsl($color);

        $this->setHsl($color, $hue, $saturation, $luminosity);
    }

    public function getLuminosity(Color $color): float
    {
        return $this->getHsl($color, self::LUMINOSITY);
    }

    public function changeOpacity(Color $color, float $change): void
    {
        $this->setOpacity($color, $color->opacity + $change);
    }

    public function changeRed(Color $color, float $change): void
    {
        $this->setRed($color, $color->red + $change);
    }

    public function setRgb(Color $color, float $red, float $green, float $blue): void
    {
        $this->setRed($color, $red);
        $this->setGreen($color, $green);
        $this->setBlue($color, $blue);
    }

    public function changeSaturation(Color $color, float $change): void
    {
        $this->setSaturation($color, $this->getSaturation($color) + $change);
    }

    public function setSaturation(Color $color, float $saturation): void
    {
        if ($saturation < 0.0 || $saturation > 1.0) {
            throw new Exceptions\InvalidValueException($saturation, 'color value');
        }

        [$hue, , $luminosity] = $this->getHsl($color);

        $this->setHsl($color, $hue, $saturation, $luminosity);
    }

    public function getSaturation(Color $color): float
    {
        return $this->getHsl($color, self::SATURATION);
    }

    public function strengthenBlue(Color $color, float $factor): void
    {
        $this->setBlue($color, $color->blue + $factor * (1.0 - $color->blue));
    }

    public function strengthenGreen(Color $color, float $factor): void
    {
        $this->setGreen($color, $color->green + $factor * (1.0 - $color->green));
    }

    public function strengthenLuminosity(Color $color, float $factor): void
    {
        $luminosity = $this->getLuminosity($color);

        $this->setLuminosity($color, $luminosity + $factor * (1.0 - $luminosity));
    }

    public function strengthenOpacity(Color $color, float $factor): void
    {
        $this->setOpacity($color, $color->opacity + $factor * (1.0 - $color->opacity));
    }

    public function strengthenRed(Color $color, float $factor): void
    {
        $this->setRed($color, $color->red + $factor * (1.0 - $color->red));
    }

    public function strengthenSaturation(Color $color, float $factor): void
    {
        $saturation = $this->getSaturation($color);

        $this->setSaturation($color, $saturation + $factor * (1.0 - $saturation));
    }

    public function strengthenTransparency(Color $color, float $factor): void
    {
        $this->setTransparency($color, 1.0 + ($factor - 1.0) * $color->opacity);
    }

    public function setTransparency(Color $color, float $transparency): void
    {
        if ($transparency < 0.0 || $transparency > 1.0) {
            throw new Exceptions\InvalidValueException($transparency, 'color value');
        }

        $color->opacity = 1.0 - $transparency;
    }

    public function toHtml(Color $color): string
    {
        return '#' . $this->toHex($color);
    }

    public function toHex(Color $color): string
    {
        $base = $this->toFF($color->red) . $this->toFF($color->green) . $this->toFF($color->blue);

        if ($this->areComparable($color->opacity, 1.0)) {
            return $base;
        }
        else {
            return $this->toFF($color->opacity) . $base;
        }
    }

    private function areComparable(float $value1, float $value2): bool
    {
        return self::isNegligible($value1 - $value2);
    }

    public function toFF(float $value): string
    {
        return str_pad(dechex($this->to255($value)), 2, '0', STR_PAD_LEFT);
    }

    public function to255(float $value): int
    {
        return (int) round(255 * $value);
    }

    public function toRgbaInt(Color $color): int
    {
        return 0x1000000 * $this->to255($this->getTransparency($color))
            + 0x10000 * $this->to255($color->red)
            + 0x100 * $this->to255($color->green)
            + $this->to255($color->blue);
    }

    public function getTransparency(Color $color): float
    {
        return 1.0 - $color->opacity;
    }

    public function toRgbInt(Color $color): int
    {
        return 0x10000 * $this->to255($color->red)
            + 0x100 * $this->to255($color->green)
            + $this->to255($color->blue);
    }

    public function changeTransparency(Color $color, float $change): void
    {
        $this->setOpacity($color, $color->opacity - $change);
    }

    public function weakenBlue(Color $color, float $factor): void
    {
        $this->setBlue($color, $factor * $color->blue);
    }

    public function weakenGreen(Color $color, float $factor): void
    {
        $this->setGreen($color, $factor * $color->green);
    }

    public function weakenLuminosity(Color $color, float $factor): void
    {
        $this->setLuminosity($color, $factor * $this->getLuminosity($color));
    }

    public function weakenOpacity(Color $color, float $factor): void
    {
        $this->setOpacity($color, $factor * $color->opacity);
    }

    public function weakenRed(Color $color, float $factor): void
    {
        $this->setRed($color, $factor * $color->red);
    }

    public function weakenSaturation(Color $color, float $factor): void
    {
        $this->setSaturation($color, $factor * $this->getSaturation($color));
    }

    public function weakenTransparency(Color $color, float $factor): void
    {
        $this->setTransparency($color, $factor * (1.0 - $color->opacity));
    }

    private function hueVarsToIntensity(float $var1, float $var2, float $varh): float
    {
        if ($varh < 0) {
            $varh += 1;
        }

        if ($varh > 1) {
            $varh -= 1;
        }

        if (6 * $varh < 1) {
            return $var1 + ($var2 - $var1) * 6 * $varh;
        }

        if (2 * $varh < 1) {
            return $var2;
        }

        if (3 * $varh < 2) {
            return $var1 + ($var2 - $var1) * (2 / 3 - $varh) * 6;
        }

        return $var1;
    }
}
