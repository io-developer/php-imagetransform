<?php

namespace iodev\Lib\ImageTransform\ExportFormats;

use Exception;
use iodev\Lib\ImageTransform\FormatType;

/**
 * @author Sedyshev Sergey
 */
abstract class ExportFormat
{
    /** 
     * @param string $type
     * @param int $quality
     * @return ExportFormat
     * @throws Exception
     */
    public static function fromType( $type, $quality=100 )
    {
        if ($type == FormatType::JPEG) {
            return new JpegExportFormat((int)$quality);
        }
        if ($type == FormatType::PNG) {
            return new PngExportFormat();
        }
        if ($type == FormatType::GIF) {
            return new GifExportFormat();
        }
        throw new Exception("Unknown format type '$type'");
    }

    /**
     * @param string $type
     * @return string
     */
    public static function typeToExtension( $type )
    {
        if ($type == FormatType::JPEG) {
            return ".jpg";
        }
        if ($type == FormatType::PNG) {
            return ".png";
        }
        if ($type == FormatType::GIF) {
            return ".gif";
        }
        return "";
    }

    /**
     * @return string
     */
    abstract public function type();
    
    /**
     * @return string
     */
    public function toExtension()
    {
        return self::typeToExtension($this->type());
    }
}
