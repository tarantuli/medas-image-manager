# medas-image-manager

Part of the [Medas framework](https://github.com/tarantuli/medas-core).

## Description

Image loading, thumbnail generation, colour analysis, and colour manipulation using the GD extension.

**Core classes:**

| Class                   | Purpose                                                                                                                                            |
|-------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------|
| `ImageManager`          | Loads images from binary content or a file path (GIF, JPEG, PNG, WBMP, WebP); creates blank transparent canvases                                   |
| `Image`                 | Thin wrapper around `\GdImage` with `width()`, `height()`, `colorAt()`, `copy()`, `makeTransparent()`, and `toPng()`                               |
| `CroppedThumbnailMaker` | Scales and centre-crops the source to exactly fill the target dimensions                                                                           |
| `ResizedThumbnailMaker` | Scales to fit within the target dimensions with transparent letterboxing; never crops                                                              |
| `ColorManager`          | Full RGB ↔ HSL conversion, WCAG relative luminance and contrast ratio, hex serialisation, channel/HSL mutation and strengthening/weakening helpers |
| `PrimaryHueFinder`      | Samples an image on a radial grid and returns the primary and secondary hues, average saturation, and average luminosity                           |

Both thumbnail makers extend `BaseThumbnailMaker`, which handles aspect-ratio inference when only one dimension is given (passing `null` for the other) and transparently caches results by content hash and target size.

## Usage

### Package developer context

Register the package and inject the services you need:

```php
use Medas\ImageManager\ImageManagerPackage;

ImageManagerPackage::instance();
```

**Loading an image:**

```php
use Medas\ImageManager\ImageManager;
use Medas\Core\Attributes\Service;

#[Service]
readonly class PhotoService
{
    public function __construct(
        private ImageManager $imageManager,
    ) {}

    public function loadFromUpload(string $binaryContent): \Medas\ImageManager\Image
    {
        // Loads from binary; writes to a temp file internally
        return $this->imageManager->fromContent($binaryContent);
    }

    public function loadFromDisk(string $path): \Medas\ImageManager\Image
    {
        return $this->imageManager->fromFile($path);
    }
}
```

Supported types: GIF, JPEG, PNG, WBMP, WebP. BMP throws `CannotReadBmpFileException`; unrecognised types throw `CannotReadFileTypeException`.

**Generating thumbnails:**

```php
use Medas\ImageManager\{CroppedThumbnailMaker, ResizedThumbnailMaker};
use Medas\Core\{Attributes\Service, File};

#[Service]
readonly class ThumbnailService
{
    public function __construct(
        private CroppedThumbnailMaker  $croppedMaker,
        private ResizedThumbnailMaker  $resizedMaker,
    ) {}

    public function avatar(File $image): File
    {
        // Exactly 200×200, centre-cropped
        return $this->croppedMaker->get($image, width: 200, height: 200);
    }

    public function preview(File $image): File
    {
        // Fits within 800×600, transparent letterboxing, never crops
        return $this->resizedMaker->get($image, width: 800, height: 600);
    }

    public function constrainWidth(File $image): File
    {
        // Width fixed at 400; height inferred from aspect ratio
        return $this->resizedMaker->get($image, width: 400, height: null);
    }

    public function constrainHeight(File $image): File
    {
        // Height fixed at 300; width inferred from aspect ratio
        return $this->resizedMaker->get($image, width: null, height: 300);
    }
}
```

Thumbnails are never upscaled — if the source already fits within the target dimensions the original `File` is returned unchanged. Results are automatically cached by content hash + dimensions via the framework cache.

**Analysing the primary hue of an image:**

```php
use Medas\ImageManager\{ImageManager};
use Medas\ImageManager\Analysis\PrimaryHueFinder;
use Medas\Core\Attributes\Service;

#[Service]
readonly class ImageAnalyser
{
    public function __construct(
        private ImageManager    $imageManager,
        private PrimaryHueFinder $primaryHueFinder,
    ) {}

    public function analyse(string $imageBinary): array
    {
        $image = $this->imageManager->fromContent($imageBinary);
        $result = $this->primaryHueFinder->find($image);

        return [
            'primary_hue'        => $result->primaryHue,       // 0–360 degrees
            'primary_strength'   => $result->primaryStrength,  // 0.0–1.0 (how dominant)
            'secondary_hue'      => $result->secondaryHue,
            'secondary_strength' => $result->secondaryStrength,
            'saturation'         => $result->saturation,       // 0.0–1.0
            'luminosity'         => $result->luminosity,       // 0.0–1.0
        ];
    }
}
```

The finder samples pixels on a radial grid (5 radii × 12 angles), weighting each pixel by its saturation and proximity to the centre. This makes the result robust against uniform backgrounds.

**Colour manipulation with `ColorManager`:**

```php
use Medas\ImageManager\{Color, ColorManager};
use Medas\Core\Attributes\Service;

#[Service]
readonly class ThemeGenerator
{
    public function __construct(
        private ColorManager $colorManager,
    ) {}

    public function generate(string $hex): array
    {
        $base = $this->colorManager->fromHtmlString($hex);

        // Create a lighter variant
        $light = $this->colorManager->fromRgba(
            $base->red, $base->green, $base->blue, $base->opacity
        );
        $this->colorManager->strengthenLuminosity($light, 0.4);

        // Create a desaturated variant
        $muted = $this->colorManager->fromRgba(
            $base->red, $base->green, $base->blue, $base->opacity
        );
        $this->colorManager->weakenSaturation($muted, 0.5);

        return [
            'base'  => $this->colorManager->toHtml($base),
            'light' => $this->colorManager->toHtml($light),
            'muted' => $this->colorManager->toHtml($muted),
        ];
    }
}
```

**WCAG contrast ratio:**

```php
$white = $this->colorManager->fromRgb(1, 1, 1);
$text  = $this->colorManager->fromHtmlString('#333333');

$ratio = $this->colorManager->getContrast($text, $white);
// WCAG AA requires ≥ 4.5:1 for normal text, ≥ 3:1 for large text
$passesAA = $ratio >= 4.5;
```

**Direct HSL manipulation:**

```php
$color = $this->colorManager->fromRgb(0.8, 0.2, 0.2);

// Rotate the hue by 120° (expressed as 0.0–1.0)
$this->colorManager->changeHue($color, 120 / 360);

// Increase luminosity toward white
$this->colorManager->strengthenLuminosity($color, 0.3);

// Set saturation directly
$this->colorManager->setSaturation($color, 0.5);

echo $this->colorManager->toHtml($color); // e.g. #4dcc4d
```

### Backend user context

This package has no CLI commands. All functionality is consumed through injected services.

**Supported image formats for loading:**

| Format | Notes                                               |
|--------|-----------------------------------------------------|
| JPEG   |                                                     |
| PNG    |                                                     |
| GIF    |                                                     |
| WebP   |                                                     |
| WBMP   |                                                     |
| BMP    | Not supported — throws `CannotReadBmpFileException` |

**Thumbnail caching** — both thumbnail makers cache results by `[class, contentHash, width, height]`. The cache backend is whatever the framework's default cache is. If the same image is requested at the same size more than once within a session, the GD operation runs only once.

**PNG output** — `Image::toPng()` and `ResizedThumbnailMaker` both output PNG. If you need a different output format, access `$image->resource` directly and use the appropriate GD function (e.g. `imagejpeg()`).
