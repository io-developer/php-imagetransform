<?php

namespace iodev\Lib\ImageTransform\Gd;

use Exception;
use iodev\Lib\ImageTransform\ContextInfo;
use iodev\Lib\ImageTransform\IContext;

/**
 * @author Sedyshev Sergey
 */
class GdContext implements IContext
{
    /**
     * @param resource $r
     * @param string $formatType
     * @param string $mimetype
     */
    public function __construct( $r=null, $formatType="", $mimetype="" )
    {
        $this->setResource($r);
        $this->setFormatType($formatType);
        $this->setMimeType($mimetype);
    }
    
    public function __destruct()
    {
        if ($this->_resource !== null) {
            imagedestroy($this->_resource);
        }
    }


    /** @var resource */
    private $_resource;
    
    /** @var int */
    private $_width;
    
    /** @var int */
    private $_height;
    
    /** @var string */
    private $_formatType;
    
    /** @var string */
    private $_mimeType;
    
    
    /**
     * @return IContext
     */
    public function fork()
    {
        throw new Exception("Not implemented");
        return clone $this;
    }
    
    /**
     * @return resource
     */
    public function getResource()
    {
        return $this->_resource;
    }
    
    /**
     * @param resource $r
     */
    public function setResource( $r )
    {
        if ($this->_resource && $this->_resource !== $r) {
            imagedestroy($this->_resource);
        }
        $this->_resource = $r;
        $this->_width = $r ? (int)imagesx($r) : 0;
        $this->_height = $r ? (int)imagesy($r) : 0;
    }
    
    
    /**
     * @return string
     */
    public function getFormatType()
    {
        return $this->_formatType;
    }
    
    /**
     * @param string $type
     */
    public function setFormatType( $type )
    {
        $this->_formatType = $type;
    }
    
    /**
     * @return string
     */
    public function getMimeType()
    {
        return $this->_mimeType;
    }
    
    /**
     * @param string $type
     */
    public function setMimeType( $type )
    {
        $this->_mimeType = $type;
    }
    
    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->_width;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->_height;
    }
    
    /**
     * @return ContextInfo
     */
    public function toContextInfo()
    {
        $info = new ContextInfo();
        $info->width = $this->_width;
        $info->height = $this->_height;
        $info->formatType = $this->_formatType;
        $info->mimeType = $this->_mimeType;
        return $info;
    }
}
