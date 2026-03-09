<?php

declare(strict_types=1);

namespace Medas\ImageManager;

use Medas\Core\{Attributes\Service, File, Interfaces\ThumbnailMaker};

#[Service]
readonly class ResizedThumbnailMaker extends BaseThumbnailMaker implements ThumbnailMaker
{
    protected function create(File $file, int $targetWidth, int $targetHeight): File
    {
        $source = $this->imageManager->fromContent($file->content);
        $currentWidth = $source->width();
        $currentHeight = $source->height();

        if ($currentWidth <= $targetWidth && $currentHeight <= $targetHeight) {
            return $file;
        }

        $sourceWidth = $currentWidth;
        $sourceHeight = $currentHeight;

        if ($currentWidth / $currentHeight > $targetWidth / $targetHeight) {
            // The source is flatter than the target
            $destinationX = 0;
            $destinationY = (int) round(($targetHeight - $sourceHeight * $targetWidth / $sourceWidth) / 2);
            $destinationWidth = $targetWidth;
            $destinationHeight = (int) round($sourceHeight * $targetWidth / $sourceWidth);
        }
        else {
            // The source is taller than the target
            $destinationX = (int) round(($targetWidth - $sourceWidth * $targetHeight / $sourceHeight) / 2);
            $destinationY = 0;
            $destinationWidth = (int) round($sourceWidth * $targetHeight / $sourceHeight);
            $destinationHeight = $targetHeight;
        }

        $targetResource = $this->imageManager->create($targetWidth, $targetHeight);

        $targetResource->makeTransparent();

        $targetResource->copy(
            $source,
            x1: $destinationX,
            y1: $destinationY,
            x0: 0,
            y0: 0,
            w1: $destinationWidth,
            h1: $destinationHeight,
            w0: $sourceWidth,
            h0: $sourceHeight,
        );

        return new File($targetResource->toPng());
    }
}
