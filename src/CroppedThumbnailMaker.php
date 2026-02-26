<?php

declare(strict_types=1);

namespace Medas\ImageManager;

use Medas\Core\{Attributes\Service, File, Interfaces\ThumbnailMaker};

#[Service]
readonly class CroppedThumbnailMaker extends BaseThumbnailMaker implements ThumbnailMaker
{
    protected function create(File $file, int $targetWidth, int $targetHeight): File
    {
        $source = $this->imageManager->fromContent($file->content);
        $currentWidth = $source->width();
        $currentHeight = $source->height();

        if ($currentWidth <= $targetWidth && $currentHeight <= $targetHeight) {
            return $file;
        }

        if ($currentWidth / $currentHeight > $targetWidth / $targetHeight) {
            // The source is flatter than the target
            $sourceWidth = (int) round($currentHeight * $targetWidth / $targetHeight);
            $sourceHeight = $currentHeight;
            $sourceX = (int) round(($currentWidth - $sourceWidth) / 2);
            $sourceY = 0;
        }
        else {
            // The source is taller than the target
            $sourceHeight = (int) round($currentWidth * $targetHeight / $targetWidth);
            $sourceWidth = $currentWidth;
            $sourceX = 0;
            $sourceY = (int) round(($currentHeight - $sourceHeight) / 2);
        }

        $targetResource = $this->imageManager->create($targetWidth, $targetHeight);

        $targetResource->copy(
            $source,
            x1: 0,
            y1: 0,
            x0: $sourceX,
            y0: $sourceY,
            w1: $targetWidth,
            h1: $targetHeight,
            w0: $sourceWidth,
            h0: $sourceHeight,
        );

        return $this->pngMaker->create($targetResource);
    }
}
