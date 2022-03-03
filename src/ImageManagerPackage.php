<?php

declare(strict_types=1);

namespace Medas\ImageManager;

use Medas\FileSystem\FileSystemPackage;
use Medas\ServiceManager\{AsSingleton, BasePackage};

class ImageManagerPackage extends BasePackage
{
    use AsSingleton;

    public function dependencies(): array
    {
        return $this->dependenciesByClass([
            FileSystemPackage::class,
        ]);
    }

    public function sourceDirectory(): string
    {
        return __DIR__;
    }
}
