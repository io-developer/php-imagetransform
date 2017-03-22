<?php

namespace iodev\Lib\ImageTransform\ExportFormats;

use iodev\Lib\ImageTransform\FormatType;

/**
 * @author Sedyshev Sergey
 */
class GifExportFormat extends ExportFormat
{
    public function __construct()
    {
    }
    
    /**
     * @return string
     */
    public function type()
    {
        return FormatType::GIF;
    }
}
