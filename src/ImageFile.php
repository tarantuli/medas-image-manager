<?php

declare(strict_types=1);

namespace Medas\ImageManager;

use Medas\Core\Interfaces\FileEntity;

class ImageFile implements FileEntity
{
    private string $content;

    public function __construct(Image $image)
    {
        $this->content = $image->toPng();
    }

    public function id(): mixed
    {
        return null;
    }

    public function setName(string|null $name): FileEntity
    {
        return $this;
    }

    public function name(): string|null
    {
        return null;
    }

    public function mimetype(): string
    {
        return 'image/png';
    }

    public function content(): string
    {
        return $this->content;
    }

    public function setContent(string $content): FileEntity
    {
        $this->content = $content;

        return $this;
    }

    public function contentHash(): string
    {
        return sha1($this->content);
    }
}
