# StulipanTraducible

[comment]: <> ([![PHP CI]&#40;https://github.com/Bacon/BaconQrCode/actions/workflows/ci.yml/badge.svg&#41;]&#40;https://github.com/Bacon/BaconQrCode/actions/workflows/ci.yml&#41;)

[comment]: <> ([![codecov]&#40;https://codecov.io/gh/Bacon/BaconQrCode/branch/master/graph/badge.svg?token=rD0HcAiEEx&#41;]&#40;https://codecov.io/gh/Bacon/BaconQrCode&#41;)

[comment]: <> ([![Latest Stable Version]&#40;https://poser.pugx.org/bacon/bacon-qr-code/v/stable&#41;]&#40;https://packagist.org/packages/bacon/bacon-qr-code&#41;)

[comment]: <> ([![Total Downloads]&#40;https://poser.pugx.org/bacon/bacon-qr-code/downloads&#41;]&#40;https://packagist.org/packages/bacon/bacon-qr-code&#41;)

[comment]: <> ([![License]&#40;https://poser.pugx.org/bacon/bacon-qr-code/license&#41;]&#40;https://packagist.org/packages/bacon/bacon-qr-code&#41;)


## Introduction
This PHP library adds translate behavior to Doctrine entities and repositories. Developed and maintained by Antonio Lilas. 


## Example usage
```php
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

$renderer = new ImageRenderer(
    new RendererStyle(400),
    new ImagickImageBackEnd()
);
$writer = new Writer($renderer);
$writer->writeFile('Hello World!', 'qrcode.png');
```

## Available image renderer back ends
BaconQrCode comes with multiple back ends for rendering images. Currently included are the following:

- `ImagickImageBackEnd`: renders raster images using the Imagick library
- `SvgImageBackEnd`: renders SVG files using XMLWriter
- `EpsImageBackEnd`: renders EPS files
