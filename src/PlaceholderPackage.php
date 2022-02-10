<?php

declare(strict_types=1);

namespace Medas\Placeholder;

use Medas\ServiceManager\BasePackage;

class PlaceholderPackage extends BasePackage
{
    public function dependencies(): array
    {
        return $this->dependenciesByClass([
        ]);
    }

    public function sourceDirectory(): string
    {
        return __DIR__;
    }
}
