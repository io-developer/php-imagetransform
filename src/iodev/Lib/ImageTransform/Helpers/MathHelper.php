<?php

namespace iodev\Lib\ImageTransform\Helpers;

/**
 * @author Sergey Sedyshev
 */
class MathHelper
{
/**
     * @param double $val
     * @param double $min
     * @param double $max
     * @return double
     */
    public static function clamp( $val, $min, $max )
    {
        return max((double)$min, min((double)$max, (double)$val));
    }
    
    /**
     * @param int $val
     * @param int $min
     * @param int $max
     * @return int
     */
    public static function clampInt( $val, $min, $max )
    {
        return max((int)$min, min((int)$max, (int)$val));
    }
    
    /**
     * @param int $val
     * @return int
     */
    public static function clampSize( $val )
    {
        return self::clampInt($val, 0, 1000000);
    }
    
    /**
     * @param int $val
     * @return int
     */
    public static function clampCoord( $val )
    {
        return self::clampInt($val, 0, 1000000);
    }
    
    /**
     * @param double $val
     * @return double
     */
    public static function clampScale( $val )
    {
        return self::clamp($val, 0.0, 1000000.0);
    }
    
    
    /**
     * @param int $srcW
     * @param int $srcH
     * @param int $dstW
     * @param int $dstH
     * @return double
     */
    public static function fitScaleInside( $srcW, $srcH, $dstW, $dstH )
    {
        return self::_fitScale($srcW, $srcH, $dstW, $dstH, "inside");
    }
    
    /**
     * @param int $srcW
     * @param int $srcH
     * @param int $dstW
     * @param int $dstH
     * @return double
     */
    public static function fitScaleOutside( $srcW, $srcH, $dstW, $dstH )
    {
        return self::_fitScale($srcW, $srcH, $dstW, $dstH, "outside");
    }
    
    /**
     * @param int $srcW
     * @param int $srcH
     * @param int $dstW
     * @param int $dstH
     * @param string $mode
     */
    private static function _fitScale( $srcW, $srcH, $dstW, $dstH, $mode )
    {
        $w = (int)$dstW;
        $h = (int)$dstH;
        
        if ($w == 0 && $h == 0) {
            return 0;
        }
        if ($h <= 0) {
            return $w / $srcW;
        }
        if ($w <= 0) {
            return $h / $srcH;
        }
        
        $sx = (int)$w / $srcW;
        $sy = (int)$h / $srcH;
        
        if ($mode == "inside") {
            return $sx > $sy ? $sy : $sx;
        }
        
        return $sx > $sy ? $sx : $sy;
    }
}
