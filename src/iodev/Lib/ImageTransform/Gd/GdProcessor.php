<?php

namespace iodev\Lib\ImageTransform\Gd;

use Exception;
use iodev\Lib\ImageTransform\ContextInfo;
use iodev\Lib\ImageTransform\ExportFormats\ExportFormat;
use iodev\Lib\ImageTransform\ExportFormats\GifExportFormat;
use iodev\Lib\ImageTransform\ExportFormats\JpegExportFormat;
use iodev\Lib\ImageTransform\ExportFormats\PngExportFormat;
use iodev\Lib\ImageTransform\FormatType;
use iodev\Lib\ImageTransform\IContext;
use iodev\Lib\ImageTransform\IProcessor;


/**
 * @author Sedyshev Sergey
 */
class GdProcessor implements IProcessor
{
    /**
     * @param int $interpDownscale
     * @param int $interpUpscale
     */
    public function __construct( $interpDownscale=0, $interpUpscale=0 )
    {
        $this->_interpDownscale = $interpDownscale ? (int)$interpDownscale : IMG_BILINEAR_FIXED;
        $this->_interpUpscale = $interpUpscale ? (int)$interpUpscale : IMG_BILINEAR_FIXED;
    }
    
    
    /** @var int */
    private $_interpDownscale;
    
    /** @var int */
    private $_interpUpscale;


    /**
     * @param int $width
     * @param int $height
     * @return IContext
     * @throws Exception
     */
    public function context( $width, $height )
    {
        $w = (int)$width;
        $h = (int)$height;
        
        if ($w < 1 || $h < 1) {
            throw new Exception("Invalid context size '$w' x '$h'");
        }
        
        return new GdContext(imagecreatetruecolor($w, $h));
    }
    
    /**
     * @param string $file
     * @return IContext
     * @throws Exception
     */
    public function contextFromFile( $file )
    {
        if (!is_file($file)) {
            throw new Exception("File not found '$file'");
        }
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimetype = finfo_file($finfo, $file);
        finfo_close($finfo);
        
        $fmtType = "";
        
        if ($mimetype == "image/jpeg") {
            $fmtType = FormatType::JPEG;
            $r = imagecreatefromjpeg($file);
        } elseif ($mimetype == "image/pjpeg") {
            $fmtType = FormatType::JPEG;
            $r = imagecreatefromjpeg($file);
        } elseif ($mimetype == "image/png") {
            $fmtType = FormatType::PNG;
            $r = imagecreatefrompng($file);
        } elseif ($mimetype == "image/gif") {
            $fmtType = FormatType::GIF;
            $r = imagecreatefromgif($file);
        } elseif ($mimetype == "image/bmp") {
            $r = imagecreatefromwbmp($file);
        } elseif ($mimetype == "image/webp") {
            $r = imagecreatefromwebp($file);
        } else {
            throw new Exception("File not found '$file'");
        }
        
        return new GdContext($r, $fmtType, $mimetype);
    }
    
    /**
     * @param IContext $ctx
     * @return IContext
     */
    public function contextClone( IContext $ctx )
    {
        $src = $this->_castContext($ctx);
        
        $dst = new GdContext();
        $dst->setFormatType($src->getFormatType());
        $dst->setMimeType($src->getMimeType());
        $dst->setResource($this->_duplicateRes($src->getResource()));
        
        return $dst;
    }
    
    /**
     * @param IContext $ctx
     * @param string $file
     * @param ExportFormat $format
     * @throws Exception
     */
    public function contextToFile( IContext $ctx, $file, ExportFormat $format )
    {
        if (empty($file)) {
            throw new Exception("Empty file path");
        }
        
        $type = $format->type();
        
        if ($type == FormatType::JPEG) {
            return $this->_saveJpeg($ctx, $file, $format);
        }
        if ($type == FormatType::PNG) {
            return $this->_savePng($ctx, $file, $format);
        }
        if ($type == FormatType::GIF) {
            return $this->_saveGif($ctx, $file, $format);
        }
        
        throw new Exception("Unsupported file format '$type' for export to '$file'");
    }
    
    /**
     * @param string $file
     * @return ContextInfo
     * @throws Exception
     */
    public function infoFromFile( $file )
    {
        if (!is_file($file)) {
            throw new Exception("File not found '$file'");
        }
        
        $sz = getimagesize($file);
        
        $info = new ContextInfo();
        $info->width = (int)$sz[0];
        $info->height = (int)$sz[1];
        $info->formatType = $this->_imagetypeToFormatType($sz[2]);
        
        return $info;
    }
    
    
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
    public function copy( IContext $ctx, IContext $dst, $x, $y, $width, $height, $dstX=0, $dstY=0 )
    {
        $gdsrc = $this->_castContext($ctx);
        $gddst = $this->_castContext($dst);
        
        imagecopy(
            $gddst->getResource()
            , $gdsrc->getResource()
            , (int)$dstX
            , (int)$dstY
            , (int)$x
            , (int)$y
            , (int)$width
            , (int)$height
        );
    }
    
    /**
     * @param IContext $ctx
     * @param int $argb
     */
    public function fill( IContext $ctx, $argb )
    {
        $gdctx = $this->_castContext($ctx);
        imagefill($gdctx->getResource(), 0, 0, $this->_createColor($ctx, $argb));
    }
    
    /**
     * @param IContext $ctx
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     * @param int $argb
     */
    public function fillRect( IContext $ctx, $x, $y, $width, $height, $argb )
    {
        $gdctx = $this->_castContext($ctx);
        
        imagefilledrectangle(
            $gdctx->getResource()
            , (int)$x
            , (int)$y
            , (int)$x + (int)$width
            , (int)$y + (int)$height
            , $this->_createColor($ctx, $argb)
        );
    }
    
    /**
     * @param IContext $ctx
     * @param float $scaleX
     * @param float $scaleY
     */
    public function scale( IContext $ctx, $scaleX, $scaleY )
    {
        $gdctx = $this->_castContext($ctx);
        
        $src = $gdctx->getResource();
        $srcW = $gdctx->getWidth();
        $srcH = $gdctx->getHeight();
        
        $w = round((double)$scaleX * $srcW);
        $h = round((double)$scaleY * $srcH);
        
        if ($srcW == $w && $srcH == $h) {
            return;
        }
        
        $gdctx->setResource($this->_duplicateRes($src, $w, $h));
    }
    
    /**
     * @param IContext $ctx
     * @param int $degreesCW
     * @param int $bgcolor
     */
    public function rotate( IContext $ctx, $degreesCW, $bgcolor=0x0 )
    {
        $degrees = $this->_cyclicDegrees(-(int)$degreesCW);
        if ($degrees == 0) {
            return;
        }
        
        $gdctx = $this->_castContext($ctx);
        $r = $gdctx->getResource();
        
        if ($degrees == 180) {
            imageflip($r, IMG_FLIP_BOTH);
        } else {
            if (function_exists("imagesetinterpolation")) {
                imagesetinterpolation($r, $this->_interpDownscale);
            }
            $bgcol = $this->_createColor($gdctx, $bgcolor);
            $r = imagerotate($r, $degrees, $bgcol, 0);
        }
        
        $gdctx->setResource($r);
    }
    
    /**
     * @param IContext $ctx
     * @param IContext $over
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     */
    public function overlay( IContext $ctx, IContext $over, $x, $y, $width, $height )
    {
        $x = (int)$x;
        $y = (int)$y;
        
        $w = (int)$width;
        $h = (int)$height;
        
        $dst = $this->_castContext($ctx);
        $dstR = $dst->getResource();
        
        $src = $this->_castContext($over);
        $srcR = $src->getResource();
        $srcW = $src->getWidth();
        $srcH = $src->getHeight();
        
        if ($srcW == $w && $srcH == $h) {
            imagecopy($dstR, $srcR, $x, $y, 0, 0, $w, $h);
        } else {
            imagecopyresampled($dstR, $srcR, $x, $y, 0, 0, $w, $h, $srcW, $srcH);
        }
    }
    
    
    /**
     * @param GdContext $ctx
     * @return GdContext
     */
    private function _castContext( GdContext $ctx )
    {
        return $ctx;
    }
    
    /**
     * @param int $w
     * @param int $h
     * @param bool $transparent
     * @param int $bgcolor
     * @return resource
     */
    private function _createRes( $w, $h, $transparent=false, $bgcolor=false )
    {
        $r = $transparent ? imagecreatetruecolor($w, $h) : imagecreate($w, $h);
        
        if ($bgcolor !== false) {
            $color = $this->_createColorRes($r, (int)$bgcolor);
            imagefill($r, 0, 0, $color);
            imagecolordeallocate($r, $color);
        }
        
        return $r;
    }
    
    /**
     * @param resource $src
     * @param int $w
     * @param int $h
     * @return resource
     */
    private function _duplicateRes( $src, $w=0, $h=0 )
    {
        $srcW = imagesx($src);
        $srcH = imagesy($src);
        
        $w = $w > 0 ? (int)$w : $srcW;
        $h = $h > 0 ? (int)$h : $srcH;
        
        $dst = $this->_createRes($w, $h, imageistruecolor($src), 0x0);
        
        if ($srcW == $w && $srcH == $h) {
            imagecopy($dst, $src, 0, 0, 0, 0, $srcW, $srcH);
            return $dst;
        }
        
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $w, $h, $srcW, $srcH);
        
        return $dst;
    }
    
    /**
     * @param GdContext $ctx
     * @param int $argb
     * @return mixed
     */
    private function _createColor( GdContext $ctx, $argb )
    {
        return $this->_createColorRes($ctx->getResource(), $argb);
    }
    
    /**
     * @param resource $r
     * @param int $argb
     * @return mixed
     */
    private function _createColorRes( $r, $argb )
    {
        $aa = ($argb >> 24) & 0xFF;
        $rr = ($argb >> 16) & 0xFF;
        $gg = ($argb >> 8) & 0xFF;
        $bb = $argb & 0xFF;
        
        $aa_ = 0x7F - (int)($aa * 0x80 / 0x100);
        
        if (imageistruecolor($r)) {
            $color = imagecolorallocatealpha($r, $rr, $gg, $bb, $aa_);
        } else {
            $color = imagecolorallocate($r, $rr, $gg, $bb);
        }
        
        if ($color === false) {
            // some error handling here
        }
        
        return $color;
    }
    
    /**
     * @param int $srcW
     * @param int $srcH
     * @param int $dstW
     * @param int $dstH
     * @return int
     */
    private function _intepmode( $srcW, $srcH, $dstW, $dstH )
    {
        $srcArea = (int)$srcW * (int)$srcH;
        $dstArea = (int)$dstW * (int)$dstH;
        return $dstArea > $srcArea ? $this->_interpUpscale : $this->_interpDownscale;
    }
    
    /**
     * @param int $degrees
     * @return int
     */
    private function _cyclicDegrees( $degrees=1 )
    {
        return (360 + ((int)$degrees % 360)) % 360;
    }
    
    /**
     * @param GdContext $ctx
     * @param string $file
     * @param JpegExportFormat $format
     * @return bool
     * @throws Exception
     */
    private function _saveJpeg( GdContext $ctx, $file, JpegExportFormat $format )
    {
        if (imagejpeg($ctx->getResource(), $file, $format->getQuality())) {
            return true;
        }
        throw new Exception("File not saved '$file'");
    }
    
    /**
     * @param GdContext $ctx
     * @param string $file
     * @param PngExportFormat $format
     * @return bool
     * @throws Exception
     */
    private function _savePng( GdContext $ctx, $file, PngExportFormat $format )
    {
        imagesavealpha($ctx->getResource(), true);
        if (imagepng($ctx->getResource(), $file)) {
            return true;
        }
        throw new Exception("File not saved '$file'");
    }
    
    /**
     * @param GdContext $ctx
     * @param string $file
     * @param GifExportFormat $format
     * @return bool
     * @throws Exception
     */
    private function _saveGif( GdContext $ctx, $file, GifExportFormat $format )
    {
        if (imagegif($ctx->getResource(), $file)) {
            return true;
        }
        throw new Exception("File not saved '$file'");
    }
    
    /**
     * @param string $type
     * @return string
     */
    private function _imagetypeToFormatType( $type )
    {
        if ($type == IMAGETYPE_JPEG || $type == IMAGETYPE_JPEG2000) {
            return FormatType::JPEG;
        }
        if ($type == IMAGETYPE_PNG) {
            return FormatType::PNG;
        }
        if ($type == IMAGETYPE_GIF) {
            return FormatType::GIF;
        }
        return "";
    }
}
