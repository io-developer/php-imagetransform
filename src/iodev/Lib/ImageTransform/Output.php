<?php

namespace iodev\Lib\ImageTransform;

use iodev\Lib\ImageTransform\ExportFormats\ExportFormat;

/**
 * @author Sergey Sedyshev
 */
class Output
{
    /** @var string */
    public $file = "";
    
    /** @var int */
    public $width = 0;
    
    /** @var int */
    public $height = 0;
    
    /** @var ExportFormat */
    public $format = null;
}
