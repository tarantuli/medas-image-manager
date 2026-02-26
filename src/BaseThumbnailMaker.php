<?php

declare(strict_types=1);

namespace Medas\ImageManager;

use Medas\Core\{File, Interfaces\CacheManager};
use Medas\Files\ContentHashManager;

abstract readonly class BaseThumbnailMaker
{
    abstract protected function create(File $file, int $targetWidth, int $targetHeight): File;

    public function __construct(
        protected CacheManager       $cacheManager,
        protected ContentHashManager $contentHashManager,
        protected ImageManager       $imageManager,
        protected PngMaker           $pngMaker,
    )
    {
    }

    public function get(File $file, int|null $width, int|null $height): File
    {
        if ($width === null && $height === null) {
            return $file;
        }

        // Derive the missing dimension from the other while preserving an aspect ratio
        if ($width === null || $height === null) {
            $source = $this->imageManager->fromContent($file->content);
            $srcW = $source->width();
            $srcH = $source->height();

            if ($width === null) {
                $width = (int) round($srcW * $height / $srcH);
            }
            else {
                $height = (int) round($srcH * $width / $srcW);
            }
        }

        return $this->cacheManager->get()->get(
            [$this::class, $this->contentHashManager->get($file), $width, $height],
            fn() => $this->create($file, $width, $height),
        );
    }
}
