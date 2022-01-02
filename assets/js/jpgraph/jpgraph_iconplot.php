<?php
//=======================================================================
// File:        JPGRAPH_ICONPLOT.PHP
// Description: Extension module to add icons to plots
// Created:     2004-02-18
// Ver:         $Id: jpgraph_iconplot.php 1404 2009-06-28 15:25:41Z ljp $
//
// Copyright (c) Asial Corporation. All rights reserved.
//========================================================================

//===================================================
// CLASS IconPlot
// Description: Make it possible to add a (small) image
// to the graph
//===================================================
class jpgraph_iconplot
{
    public  $iX              = 0;
    public  $iY              = 0;
    public  $iScale          = 1.0;
    public  $iMix            = 100;
    private $iHorAnchor      = 'left';
    private $iVertAnchor     = 'top';
    private $iFile           = '';
    private $iAnchors        = ['left', 'right', 'top', 'bottom', 'center'];
    private $iCountryFlag    = '';
    private $iCountryStdSize = 3;
    private $iScalePosY      = null;
    private $iScalePosX      = null;
    private $iImgString      = '';

    public function __construct($aFile = '', $aX = 0, $aY = 0, $aScale = 1.0, $aMix = 100)
    {
        $this->iFile  = $aFile;
        $this->iX     = $aX;
        $this->iY     = $aY;
        $this->iScale = $aScale;
        if ($aMix < 0 || $aMix > 100) {
            JpGraphError::raiseL(8001); //('Mix value for icon must be between 0 and 100.');
        }
        $this->iMix = $aMix;
    }

    public function SetCountryFlag($aFlag, $aX = 0, $aY = 0, $aScale = 1.0, $aMix = 100, $aStdSize = 3): void
    {
        $this->iCountryFlag = $aFlag;
        $this->iX           = $aX;
        $this->iY           = $aY;
        $this->iScale       = $aScale;
        if ($aMix < 0 || $aMix > 100) {
            JpGraphError::raiseL(8001); //'Mix value for icon must be between 0 and 100.');
        }
        $this->iMix            = $aMix;
        $this->iCountryStdSize = $aStdSize;
    }

    public function SetPos($aX, $aY): void
    {
        $this->iX = $aX;
        $this->iY = $aY;
    }

    public function CreateFromString($aStr): void
    {
        $this->iImgString = $aStr;
    }

    public function SetScalePos($aX, $aY): void
    {
        $this->iScalePosX = $aX;
        $this->iScalePosY = $aY;
    }

    public function setScale($aScale): void
    {
        $this->iScale = $aScale;
    }

    public function SetMix($aMix): void
    {
        if ($aMix < 0 || $aMix > 100) {
            JpGraphError::raiseL(8001); //('Mix value for icon must be between 0 and 100.');
        }
        $this->iMix = $aMix;
    }

    public function SetAnchor($aXAnchor = 'left', $aYAnchor = 'center'): void
    {
        if (!in_array($aXAnchor, $this->iAnchors, true)
            || !in_array($aYAnchor, $this->iAnchors, true)) {
            JpGraphError::raiseL(8002); //("Anchor position for icons must be one of 'top', 'bottom', 'left', 'right' or 'center'");
        }
        $this->iHorAnchor  = $aXAnchor;
        $this->iVertAnchor = $aYAnchor;
    }

    public function PreStrokeAdjust($aGraph): void
    {
        // Nothing to do ...
    }

    public function DoLegend($aGraph): void
    {
        // Nothing to do ...
    }

    public function Max()
    {
        return [false, false];
    }

    // The next four function are framework function tht gets called
    // from Gantt and is not menaiungfull in the context of Icons but
    // they must be implemented to avoid errors.
    public function GetMaxDate()
    {
        return false;
    }

    public function GetMinDate()
    {
        return false;
    }

    public function GetLineNbr()
    {
        return 0;
    }

    public function GetAbsHeight()
    {
        return 0;
    }

    public function Min()
    {
        return [false, false];
    }

    public function StrokeMargin(&$aImg)
    {
        return true;
    }

    public function Stroke($aImg, $axscale = null, $ayscale = null): void
    {
        $this->StrokeWithScale($aImg, $axscale, $ayscale);
    }

    public function StrokeWithScale($aImg, $axscale, $ayscale): void
    {
        if (null === $this->iScalePosX || null === $this->iScalePosY
            || null === $axscale
            || null === $ayscale) {
            $this->_Stroke($aImg);
        } else {
            $this->_Stroke($aImg, round($axscale->Translate($this->iScalePosX)), round($ayscale->Translate($this->iScalePosY)));
        }
    }

    public function GetWidthHeight()
    {
        $dummy = 0;

        return $this->_Stroke($dummy, null, null, true);
    }

    public function _Stroke($aImg, $x = null, $y = null, $aReturnWidthHeight = false)
    {
        if ('' != $this->iFile && '' != $this->iCountryFlag) {
            JpGraphError::raiseL(8003); //('It is not possible to specify both an image file and a country flag for the same icon.');
        }
        if ('' != $this->iFile) {
            $gdimg = Graph::loadBkgImage('', $this->iFile);
        } elseif ('' != $this->iImgString) {
            $gdimg = Image::CreateFromString($this->iImgString);
        } else {
            if (!class_exists('FlagImages', false)) {
                JpGraphError::raiseL(8004); //('In order to use Country flags as icons you must include the "jpgraph_flags.php" file.');
            }
            $fobj  = new FlagImages($this->iCountryStdSize);
            $dummy = '';
            $gdimg = $fobj->GetImgByName($this->iCountryFlag, $dummy);
        }

        $iconw = imagesx($gdimg);
        $iconh = imagesy($gdimg);

        if ($aReturnWidthHeight) {
            return [round($iconw * $this->iScale), round($iconh * $this->iScale)];
        }

        if (null !== $x && null !== $y) {
            $this->iX = $x;
            $this->iY = $y;
        }
        if ($this->iX >= 0 && $this->iX <= 1.0) {
            $w        = imagesx($aImg->img);
            $this->iX = round($w * $this->iX);
        }
        if ($this->iY >= 0 && $this->iY <= 1.0) {
            $h        = imagesy($aImg->img);
            $this->iY = round($h * $this->iY);
        }

        if ('center' == $this->iHorAnchor) {
            $this->iX -= round($iconw * $this->iScale / 2);
        }
        if ('right' == $this->iHorAnchor) {
            $this->iX -= round($iconw * $this->iScale);
        }
        if ('center' == $this->iVertAnchor) {
            $this->iY -= round($iconh * $this->iScale / 2);
        }
        if ('bottom' == $this->iVertAnchor) {
            $this->iY -= round($iconh * $this->iScale);
        }

        $aImg->CopyMerge($gdimg, $this->iX, $this->iY, 0, 0, round($iconw * $this->iScale), round($iconh * $this->iScale), $iconw, $iconh, $this->iMix);
    }
}
