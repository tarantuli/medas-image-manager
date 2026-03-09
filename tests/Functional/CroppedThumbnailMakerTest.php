<?php

declare(strict_types=1);

namespace Medas\ImageManagerTest\Functional;

use Medas\Core\File;
use Medas\ImageManager\{CroppedThumbnailMaker, ImageManager};
use PHPUnit\Framework\TestCase;

class CroppedThumbnailMakerTest extends TestCase
{
    public function testCreateSquare(): void
    {
        $thumbnail = $this->getThumbnail(100, 100);

        self::assertEquals(
            file_get_contents(__DIR__ . '/../MockUps/pino-cropped-thumbnail-100x100.png'),
            $thumbnail->content
        );
    }

    public function testCreateLandscape(): void
    {
        $thumbnail = $this->getThumbnail(300, 100);

        self::assertEquals(
            file_get_contents(__DIR__ . '/../MockUps/pino-cropped-thumbnail-300x100.png'),
            $thumbnail->content
        );
    }

    public function testCreatePortrait(): void
    {
        $thumbnail = $this->getThumbnail(100, 300);

        self::assertEquals(
            file_get_contents(__DIR__ . '/../MockUps/pino-cropped-thumbnail-100x300.png'),
            $thumbnail->content
        );
    }

    private function getThumbnail(int $width, int $height): File
    {
        $maker = service(CroppedThumbnailMaker::class);

        return $maker->get($this->getTestSource(), $width, $height);
    }

    private function getTestSource(): File
    {
        $image = service(ImageManager::class)->fromFile(__DIR__ . '/../MockUps/pino-333x500.jpg');

        return new File($image->toPng());
    }
}
