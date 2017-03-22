<?php

namespace iodev\Lib\ImageTransform;

/**
 * @author Sedyshev Sergey
 */
interface IContext
{
    /**
     * @return IContext
     */
    function fork();
    
    /**
     * @return string
     */
    function getFormatType();
    
    /**
     * @return string
     */
    function getMimeType();
    
    /**
     * @return int
     */
    function getWidth();
    
    /**
     * @return int
     */
    function getHeight();
    
    /**
     * @return ContextInfo
     */
    function toContextInfo();
}
