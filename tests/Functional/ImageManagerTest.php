<?php

declare(strict_types=1);

namespace Medas\ImageManagerTest\Functional;

use Medas\ImageManager\{Exceptions, ImageManager};
use PHPUnit\Framework\{Attributes\DataProvider, TestCase};

class ImageManagerTest extends TestCase
{
    public static function notImages(): array
    {
        return [
            'text' => ["Just some text\n"],
            'empty' => [''],

            // Too short for exif_imagetype() to read a header
            'one byte' => ['x'],
        ];
    }

    public function testLoadsAnImageFromContent(): void
    {
        $image = $this->imageManager()->fromContent($this->png(40, 30));

        self::assertSame(40, $image->width());
        self::assertSame(30, $image->height());
    }

    /**
     * Regression: exif_imagetype() returns false for content that isn't an image, which the exception's int
     * parameter refused: a TypeError instead of CannotReadFileTypeException
     */
    #[DataProvider('notImages')]
    public function testContentThatIsntAnImageIsRefused(string $content): void
    {
        $this->expectException(Exceptions\CannotReadFileTypeException::class);
        $this->expectExceptionMessageMatches('/with type unknown$/');
        $this->imageManager()->fromContent($content);
    }

    public function testAnUnsupportedImageTypeNamesItsType(): void
    {
        $this->expectException(Exceptions\CannotReadFileTypeException::class);
        $this->expectExceptionMessageMatches('#with type image/tiff$#');

        // The header of a little-endian TIFF, which exif_imagetype() recognises
        $this->imageManager()->fromContent("II*\0" . str_repeat("\0", 64));
    }

    public function testABmpIsRefused(): void
    {
        $image = imagecreatetruecolor(4, 4);

        ob_start();

        imagebmp($image);

        $bmp = ob_get_clean();

        $this->expectException(Exceptions\CannotReadBmpFileException::class);
        $this->imageManager()->fromContent($bmp);
    }

    /**
     * A file with an image's header but not its data: GD emitted warnings on the way to the CannotReadFileException,
     * which the aggressive error policy turns into an ErrorException
     */
    public function testACorruptImageIsRefused(): void
    {
        $corrupt = substr($this->png(40, 30), 0, 40);
        $warnings = [];

        // Like any well-behaved handler (and the medas error policies), this one leaves errors suppressed with @ alone
        set_error_handler(function (int $severity, string $message) use (&$warnings): bool {
            if (error_reporting() & $severity) {
                $warnings[] = $message;
            }

            return true;
        });

        try {
            $this->assertThrowsCannotReadFile(fn() => $this->imageManager()->fromContent($corrupt));
        }

        finally{
            restore_error_handler();
        }

        // Under a strict error policy, a warning would have become an ErrorException instead
        self::assertSame([], $warnings);
    }

    private function png(int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);

        ob_start();

        imagepng($image);

        return ob_get_clean();
    }

    private function assertThrowsCannotReadFile(callable $code): void
    {
        try {
            $code();
        }
        catch (Exceptions\CannotReadFileException) {
            $this->addToAssertionCount(1);

            return;
        }

        self::fail('Expected CannotReadFileException');
    }

    public function testAMissingFileIsRefused(): void
    {
        $this->expectException(Exceptions\CannotReadFileException::class);
        $this->imageManager()->fromFile(__DIR__ . '/does-not-exist.png');
    }

    private function imageManager(): ImageManager
    {
        return service(ImageManager::class);
    }
}
