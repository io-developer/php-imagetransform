<?php

namespace iodev\Lib\ImageTransform;

use Exception;
use iodev\Lib\ImageTransform\ExportFormats\ExportFormat;

/**
 * @author Sedyshev Sergey
 */
interface IProcessor
{
    /**
     * @param int $width
     * @param int $height
     * @return IContext
     * @throws Exception
     */
    function context( $width, $height );
    
    /**
     * @param string $file
     * @return IContext
     * @throws Exception
     */
    function contextFromFile( $file );
    
    /**
     * @param IContext $ctx
     * @return IContext
     */
    function contextClone( IContext $ctx );
    
    /**
     * @param IContext $ctx
     * @param string $file
     * @param ExportFormat $format
     * @throws Exception
     */
    function contextToFile( IContext $ctx, $file, ExportFormat $format );
    
    /**
     * @param string $file
     * @return ContextInfo
     * @throws Exception
     */
    function infoFromFile( $file );
    
    /**
     * @param IContext $ctx
     * @param IContext $dst
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     * @param int $dstX
     * @param int $dstY
     */
    function copy( IContext $ctx, IContext $dst, $x, $y, $width, $height, $dstX=0, $dstY=0 );
    
    /**
     * @param IContext $ctx
     * @param int $argb
     */
    function fill( IContext $ctx, $argb );
    
    /**
     * @param IContext $ctx
     * @param float $scaleX
     * @param float $scaleY
     */
    function scale( IContext $ctx, $scaleX, $scaleY );
    
    /**
     * @param IContext $ctx
     * @param int $degreesCW
     * @param int $bgcolor
     */
    function rotate( IContext $ctx, $degreesCW, $bgcolor=0x0 );
    
    /**
     * @param IContext $ctx
     * @param IContext $over
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     */
    function overlay( IContext $ctx, IContext $over, $x, $y, $width, $height );
}
