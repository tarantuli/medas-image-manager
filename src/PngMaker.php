<?php

declare(strict_types=1);

namespace Medas\ImageManager;

use Medas\Core\{Attributes\Service, File};

#[Service]
readonly class PngMaker
{
    public function create(Image $image): File
    {
        return new File($image->toPng(), mimetype: 'image/png');
    }
}
