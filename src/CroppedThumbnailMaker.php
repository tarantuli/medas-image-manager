<?php

declare(strict_types=1);

namespace Medas\ImageManager;

use Medas\Core\FileEntity;
use Medas\Core\ThumbnailMaker;
use Medas\ServiceManager\Attributes\Service;
use Medas\ServiceManager\Interfaces\Cache;

#[Service]
class CroppedThumbnailMaker implements ThumbnailMaker
{
    public function __construct(
        private Cache        $cache,
        private ImageManager $imageManager,
    )
    {
    }

    public function get(FileEntity $file, int|null $width, int|null $height): FileEntity
    {
        if ($width === null && $height === null) {
            return $file;
        }

        return $this->cache->get([$this::class, $file->contentHash(), $width, $height], function () use ($file, $width, $height) {
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

        $targetResource = $this->imageManager->create($targetWidth, $targetHeight);

        if ($currentWidth / $currentHeight > $targetWidth / $targetHeight) {
            // The source is flatter than the target
            $w0 = (int) round($currentHeight * $targetWidth / $targetHeight);
            $h0 = $currentHeight;
            $x0 = (int) round(($currentWidth - $w0) / 2);
            $y0 = 0;
        }
        else {
            // The source is taller than the target
            $h0 = (int) round($currentWidth * $targetHeight / $targetWidth);
            $w0 = $currentWidth;
            $x0 = 0;
            $y0 = (int) round(($currentHeight - $h0) / 2);
        }

        // Always the same
        $x1 = 0;
        $y1 = 0;
        $w1 = $targetWidth;
        $h1 = $targetHeight;

        // The actual cut
        $targetResource->copy($source, $x1, $y1, $x0, $y0, $w1, $h1, $w0, $h0);

        return new ImageFile($targetResource);
    }
}
