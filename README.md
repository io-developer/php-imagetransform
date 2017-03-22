# ImageTransform
PHP library for easy image resizing and cropping

## Requirements
PHP >= 5.4
- gd


## Usage

```php
use iodev\Lib\ImageTransform\ImageTransformFactory;

ImageTransformFactory::create()
    ->inputFile("source.jpg")
    ->cropOuter(1280, 720)
    ->reduce(200, 200)
    ->exportFileWithInputFormat("output.jpg");
```
