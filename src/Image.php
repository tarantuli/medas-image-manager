<?php

declare(strict_types=1);

namespace Medas\ImageManager;

class Image
{
    public function __construct(private readonly \GdImage $resource)
    {
    }

    public function width(): int
    {
        return imagesx($this->resource);
    }

    public function height(): int
    {
        return imagesy($this->resource);
    }

    public function colorAt(int $x, int $y): array
    {
        return imagecolorsforindex($this->resource, imagecolorat($this->resource, $x, $y));
    }

    public function copy(Image $source, int $x1, int $y1, int $x0, int $y0, int $w1, int $h1, int $w0, int $h0): void
    {
        imagecopyresampled($this->resource, $source->resource, $x1, $y1, $x0, $y0, $w1, $h1, $w0, $h0);
    }

    public function makeTransparent(): void
    {
        imagefill($this->resource, 0, 0,
            imagecolorallocatealpha($this->resource, 0, 0, 0, 127)
        );
    }

    public function toPng(): string
    {
        ob_start();
        imagepng($this->resource);

        return ob_get_clean();
    }
}
