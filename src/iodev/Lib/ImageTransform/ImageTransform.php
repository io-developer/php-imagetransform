<?php

namespace iodev\Lib\ImageTransform;

use iodev\Lib\ImageTransform\ExportFormats\ExportFormat;
use iodev\Lib\ImageTransform\Helpers\MathHelper;
use iodev\Lib\ImageTransform\Output;

/**
 * @author Sedyshev Sergey
 */
class ImageTransform
{
    /**
     * @param IProcessor $processor
     * @param bool $mkdirOnExport
     * @param int $mkdirMode
     */
    public function __construct( IProcessor $processor, $mkdirOnExport=true, $mkdirMode=0755 )
    {
        $this->_processor = $processor;
        $this->_mkdirOnExport = $mkdirOnExport;
        $this->_mkdirMode = $mkdirMode;
    }
    
    
    /** @var IProcessor */
    private $_processor;
    
    /** @var bool */
    private $_mkdirOnExport;
    
    /** @var int */
    private $_mkdirMode;
    
    /** @var IContext */
    private $_ctx;
    
    /** @var string */
    private $_inputFormatType;
    
    
    /**
     * @return IProcessor
     */
    public function getProcessor()
    {
        return $this->_processor;
    }
    
    /**
     * @return IContext
     */
    public function getContext()
    {
        return $this->_ctx;
    }
    
    /**
     * @return string
     */
    public function getInputFormatType()
    {
        return $this->_inputFormatType;
    }
    
    /**
     * @param string $file
     * @return ContextInfo
     */
    public function readInfoFromFile( $file )
    {
        return $this->_processor->infoFromFile($file);
    }

    /**
     * @param IContext $ctx
     */
    public function inputContext( IContext $ctx )
    {
        $this->_ctx = $ctx;
        $this->_inputFormatType = $ctx->getFormatType();
        return $this;
    }
    
    /**
     * @param IContext $ctx
     */
    public function inputContextCloned( IContext $ctx )
    {
        return $this->inputContext($this->_processor->contextClone($ctx));
    }
    
    /**
     * @param string $file
     */
    public function inputFile( $file )
    {
        return $this->inputContext($this->_processor->contextFromFile($file));
    }
    
    
    /**
     * @return ImageTransform
     */
    public function forkTransform()
    {
        $m = new ImageTransform($this->_processor);
        if ($this->_ctx) {
            $m->inputContext($this->_ctx->fork());
        }
        return $m;
    }
    
    /**
     * @return ImageTransform
     */
    public function cloneTransform()
    {
        $m = new ImageTransform($this->_processor);
        if ($this->_ctx) {
            $m->inputContextCloned($this->_ctx);
        }
        return $m;
    }
    
    
    /**
     * @param float $factor
     * @return ImageTransform
     */
    public function scale( $factor )
    {
        $sxy = MathHelper::clampScale($factor);
        $this->_processor->scale($this->_ctx, $sxy, $sxy);
        return $this;
    }
    
    /**
     * @param float $sx
     * @param float $sy
     * @return ImageTransform
     */
    public function scaleXY( $sx, $sy )
    {
        $sx_ = MathHelper::clampScale($sx);
        $sy_ = MathHelper::clampScale($sy);
        $this->_processor->scale($this->_ctx, $sx_, $sy_);
        return $this;
    }
    
    /**
     * @param int $width
     * @param int $height
     * @return ImageTransform
     */
    public function stretch( $width, $height )
    {
        $sx = (int)$width / (int)$this->_ctx->getWidth();
        $sy = (int)$height / (int)$this->_ctx->getHeight();
        $this->scaleXY($sx, $sy);
        return $this;
    }
    
    /**
     * @param int $width
     * @param int $height
     * @return ImageTransform
     */
    public function fit( $width=0, $height=0 )
    {
        $k = MathHelper::fitScaleInside($this->_ctx->getWidth(), $this->_ctx->getHeight(), $width, $height);
        $this->scale($k);
        return $this;
    }
    
    /**
     * @param int $width
     * @param int $height
     * @return ImageTransform
     */
    public function fitOuter( $width, $height )
    {
        $k = MathHelper::fitScaleOutside($this->_ctx->getWidth(), $this->_ctx->getHeight(), $width, $height);
        $this->scale($k);
        return $this;
    }
    
    /**
     * @param int $width
     * @param int $height
     * @return ImageTransform
     */
    public function fitOuterCrop( $width, $height )
    {
        return $this
            ->fitOuter($width, $height)
            ->cropOuter($width, $height);
    }
    
    
    /**
     * @param int $width
     * @param int $height
     * @return ImageTransform
     */
    public function reduce( $width=0, $height=0 )
    {
        $k = MathHelper::fitScaleInside($this->_ctx->getWidth(), $this->_ctx->getHeight(), $width, $height);
        if ($k < 1.0) {
            $this->scale($k);
        }
        return $this;
    }
    
    
    /**
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     * @return ImageTransform
     */
    public function crop( $x, $y, $width, $height )
    {
        $src = $this->_ctx;
        
        $x_ = MathHelper::clampCoord($x);
        $y_ = MathHelper::clampCoord($y);
        $w_ = MathHelper::clampSize($width);
        $h_ = MathHelper::clampSize($height);
        
        if ($x_ == 0 && $y_ == 0 && $w_ == $src->getWidth() && $h_ == $src->getHeight()) {
            return $this;
        }
        
        $dst = $this->_processor->context($w_, $h_);
        
        $this->_processor->copy($src, $dst, $x_, $y_, $w_, $h_);
        $this->_ctx = $dst;
        
        return $this;
    }
    
    /**
     * @param int $left
     * @param int $top
     * @param int $right
     * @param int $bottom
     * @return ImageTransform
     */
    public function cropCoord( $left, $top, $right, $bottom )
    {
        $x = (int)$left;
        $y = (int)$top;
        $w = (int)$right - $x;
        $h = (int)$bottom - $y;
        return $this->crop($x, $y, $w, $h);
    }
    
    /**
     * @param int $width
     * @param int $height
     * @return ImageTransform
     */
    public function cropOuter( $width, $height )
    {
        $w = (int)$width;
        $h = (int)$height;
        $x = round(0.5 * ($this->_ctx->getWidth() - $w));
        $y = round(0.5 * ($this->_ctx->getHeight() - $h));
        return $this->crop($x, $y, $w, $h);
    }
    
    /**
     * @param int $length
     * @return ImageTransform
     */
    public function cropOuterSquare( $length=0 )
    {
        $w = $this->_ctx->getWidth();
        $h = $this->_ctx->getHeight();
        
        $l = (int)$length;
        if ($l <= 0) {
            $l = min($w, $h);
        }
        
        $x = round(0.5 * ($l - $w));
        $y = round(0.5 * ($l - $h));
        
        return $this->crop($x, $y, $l, $l);
    }
    
    /**
     * @param int $degreesCW
     * @param int $bgcolor
     * @return ImageTransform
     */
    public function rotate( $degreesCW, $bgcolor=0x0 )
    {
        $this->_processor->rotate($this->_ctx, (int)$degreesCW, (int)$bgcolor);
        return $this;
    }
    
    /**
     * @param int $degreesCCW
     * @return ImageTransform
     */
    public function rotateCCW( $degreesCCW=0 )
    {
        return $this->rotate(-(int)$degreesCCW);
    }
    
    
    /**
     * @param IContext $ctx
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     * @return ImageTransform
     */
    public function overlay( IContext $ctx, $x=0, $y=0, $width=0, $height=0 )
    {
        $w_ = MathHelper::clampSize($width);
        $w_ = $w_ > 0 ? $w_ : $ctx->getWidth();
        
        $h_ = MathHelper::clampSize($height);
        $h_ = $h_ > 0 ? $h_ : $ctx->getHeight();
        
        $this->_processor->overlay($this->_ctx, $ctx, (int)$x, (int)$y, $w_, $h_);
        return $this;
    }
    
    /**
     * @param string $file
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     * @return ImageTransform
     */
    public function overlayFile( $file, $x=0, $y=0, $width=0, $height=0 )
    {
        $ctx = $this->_processor->contextFromFile($file);
        return $this->overlay($ctx, $x, $y, $width, $height);
    }
    
    /**
     * @param IContext $ctx
     * @param double $wxFactor
     * @param double $wyFactor
     * @param double $areaFactor
     * @return ImageTransform
     */
    public function overlayRelative( IContext $ctx, $wxFactor=0, $wyFactor=0, $areaFactor=0.5 )
    {
        $srcW = $this->_ctx->getWidth();
        $srcH = $this->_ctx->getHeight();
        $srcArea = $srcW * $srcH + 1;
        
        $ctxW = $ctx->getWidth();
        $ctxH = $ctx->getHeight();
        $ctxArea = $ctxW * $ctxH + 1;
        
        $k = $areaFactor * sqrt($srcArea / $ctxArea);
        
        $w = $k * $ctxW;
        $h = $k * $ctxH;
        
        if ($w > $srcW) {
            $h *= $srcW / $w;
            $w = $srcW;
        }
        
        if ($h > $srcH) {
            $w *= $srcH / $h;
            $h = $srcH;
        }
        
        $x = $wxFactor * $w;
        $y = $wyFactor * $w;
        
        return $this->overlay($ctx, $x, $y, $w, $h);
    }
    
    /**
     * @param string $file
     * @param double $wxFactor
     * @param double $wyFactor
     * @param double $areaFactor
     * @return ImageTransform
     */
    public function overlayRelativeFile( $file, $wxFactor=0, $wyFactor=0, $areaFactor=0.5 )
    {
        $ctx = $this->_processor->contextFromFile($file);
        return $this->overlayRelative($ctx, $wxFactor, $wyFactor, $areaFactor);
    }
    
    
    /**
     * @param string $file
     * @param ExportFormat $format
     * @param Output $exportResult
     * @return ImageTransform
     */
    public function exportFile( $file, ExportFormat $format, $exportResult=null )
    {
        $this->_mkdirIfNeeded($file);
        $this->_processor->contextToFile($this->_ctx, $file, $format);
        if ($exportResult) {
            $exportResult->file = $file;
            $exportResult->width = $this->_ctx->getWidth();
            $exportResult->height = $this->_ctx->getHeight();
            $exportResult->format = $format;
        }
        return $this;
    }
    
    /**
     * @param string $file
     * @param ExportFormat $format
     * @param Output $exportResult
     * @return string
     */
    public function exportFileWithFormatExt( $file, ExportFormat $format, $exportResult=null )
    {
        $info = pathinfo($file);
        $dstFile = ""
            . $info["dirname"]
            . DIRECTORY_SEPARATOR
            . $info["filename"]
            . ExportFormat::typeToExtension($format->type());
        
        $this->exportFile($dstFile, $format, $exportResult);
        
        return $this;
    }
    
    /**
     * @param string $file
     * @param int $exportQuality
     * @param Output $exportResult
     * @return string
     */
    public function exportFileWithInputFormat( $file, $exportQuality=100, $exportResult=null )
    {
        $this->exportFileWithFormatExt(
            $file
            , ExportFormat::fromType($this->_inputFormatType, $exportQuality)
            , $exportResult
        );
        return $this;
    }
    
    /**
     * @param string $file
     */
    private function _mkdirIfNeeded( $file )
    {
        if (!$this->_mkdirOnExport) {
            return;
        }
        $dir = pathinfo($file, PATHINFO_DIRNAME);
        if (!is_dir($dir)) {
            mkdir($dir, $this->_mkdirMode, true);
        }
    }
}
