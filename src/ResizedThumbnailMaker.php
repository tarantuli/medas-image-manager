<?php

declare(strict_types=1);

namespace Medas\ImageManager;

use Medas\Core\FileEntity;
use Medas\Core\ThumbnailMaker;
use Medas\ServiceManager\Attributes\Service;
use Medas\ServiceManager\Cache\CacheManager;

#[Service]
class ResizedThumbnailMaker implements ThumbnailMaker
{
    public function __construct(
        private CacheManager $cacheManager,
        private ImageManager $imageManager,
    )
    {
    }

    public function get(FileEntity $file, int|null $width, int|null $height): FileEntity
    {
        if ($width === null && $height === null) {
            return $file;
        }

        return $this->cacheManager->get()->get([$this::class, $file->contentHash(), $width, $height],
            function () use ($file, $width, $height) {
                return $this->create($file, $width, $height);
            });
    }

    private function create(FileEntity $file, int $targetWidth, int $targetHeight): FileEntity
    {
        $source = $this->imageManager->fromContent($file->content());
        $currentWidth = $source->width();
        $currentHeight = $source->height();

        if ($currentWidth <= $targetWidth && $currentHeight <= $targetHeight) {
            return $file;
        }

        // Always the same
        $sourceWidth = $currentWidth;
        $sourceHeight = $currentHeight;
        $sourceX = 0;
        $sourceY = 0;

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

        // The actual cut
        $targetResource = $this->imageManager->create($targetWidth, $targetHeight);
        $targetResource->makeTransparent();
        $targetResource->copy(
            $source,
            $destinationX, $destinationY, $sourceX, $sourceY,
            $destinationWidth, $destinationHeight, $sourceWidth, $sourceHeight
        );

        return new ImageFile($targetResource);
    }
}
