<?php

namespace iodev\Lib\ImageTransform;

use iodev\Lib\ImageTransform\Gd\GdProcessor;

/**
 * @author Sergey Sedyshev
 */
class ImageTransformFactory
{
    /**
     * @return ImageTransform
     */
    public static function create()
    {
        return self::createGd();
    }
    
    /**
     * @return ImageTransform
     */
    public static function createGd()
    {
        return new ImageTransform(new GdProcessor());
    }
}
