<?php

declare(strict_types=1);

namespace Medas\ImageManager;

use Medas\Core\Attributes\Service;
use Medas\Core\Interfaces\{CacheManager, FileEntity, ThumbnailMaker};

#[Service]
class CroppedThumbnailMaker implements ThumbnailMaker
{
    public function __construct(
        private readonly CacheManager $cacheManager,
        private readonly ImageManager $imageManager,
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

        // Always the same
        $targetX = 0;
        $targetY = 0;

        // The actual cut
        $targetResource = $this->imageManager->create($targetWidth, $targetHeight);
        $targetResource->copy(
            $source,
            $targetX, $targetY, $sourceX, $sourceY,
            $targetWidth, $targetHeight, $sourceWidth, $sourceHeight
        );

        return new ImageFile($targetResource);
    }
}
