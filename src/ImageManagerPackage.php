<?php

declare(strict_types=1);

namespace Medas\ImageManager;

use Medas\Core\{AsSingleton, BasePackage};
use Medas\FileSystem\FileSystemPackage;
use Medas\Files\FilesPackage;

class ImageManagerPackage extends BasePackage
{
    use AsSingleton;

    public function dependencies(): array
    {
        return [
            FilesPackage::instance(),
            FileSystemPackage::instance(),
        ];
    }

    public function sourceDirectory(): string
    {
        return __DIR__;
    }
}
