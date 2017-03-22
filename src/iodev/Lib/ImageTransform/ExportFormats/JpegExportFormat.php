<?php

namespace iodev\Lib\ImageTransform\ExportFormats;

use iodev\Lib\ImageTransform\FormatType;

/**
 * @author Sedyshev Sergey
 */
class JpegExportFormat extends ExportFormat
{
    public function __construct( $quality=100 )
    {
        $this->setQuality($quality);
    }
    
    
    /** @var int */
    private $_quality;
    
    
    /**
     * @return string
     */
    public function type()
    {
        return FormatType::JPEG;
    }
    
    /**
     * @return int
     */
    public function getQuality()
    {
        return $this->_quality;
    }
    
    /**
     * 
     * @param int $val
     */
    private function setQuality( $val )
    {
        $this->_quality = max(0, min(100, (int)$val));
    }
}
