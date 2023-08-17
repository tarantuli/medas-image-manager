<?php

declare(strict_types=1);

namespace Medas\ImageManager;

use Medas\Core\AsSingleton;
use Medas\FileSystem\FileSystemPackage;
use Medas\ServiceManager\BasePackage;

class ImageManagerPackage extends BasePackage
{
    use AsSingleton;

    public function dependencies(): array
    {
        return [
            FileSystemPackage::instance(),
        ];
    }

    public function sourceDirectory(): string
    {
        return __DIR__;
    }
}
