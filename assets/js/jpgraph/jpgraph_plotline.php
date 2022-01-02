<?php

/*=======================================================================
 // File:        JPGRAPH_PLOTLINE.PHP
 // Description: PlotLine extension for JpGraph
 // Created:     2009-03-24
 // Ver:         $Id: jpgraph_plotline.php 1931 2010-03-22 15:05:48Z ljp $
 //
 // CLASS PlotLine
 // Data container class to hold properties for a static
 // line that is drawn directly in the plot area.
 // Useful to add static borders inside a plot to show for example set-values
 //
 // Copyright (c) Asial Corporation. All rights reserved.
 //========================================================================
 */

class jpgraph_plotline
{
    public    $scaleposition;
    public    $direction           = -1;
    protected $weight              = 1;
    protected $color               = 'black';
    private   $legend              = '';
    private   $hidelegend          = false;
    private   $legendcsimtarget    = '';
    private   $legendcsimalt       = '';
    private   $legendcsimwintarget = '';
    private   $iLineStyle          = 'solid';
    public    $numpoints           = 0; // Needed since the framework expects this property

    public function __construct($aDir = HORIZONTAL, $aPos = 0, $aColor = 'black', $aWeight = 1)
    {
        $this->direction     = $aDir;
        $this->color         = $aColor;
        $this->weight        = $aWeight;
        $this->scaleposition = $aPos;
    }

    public function SetLegend($aLegend, $aCSIM = '', $aCSIMAlt = '', $aCSIMWinTarget = ''): void
    {
        $this->legend              = $aLegend;
        $this->legendcsimtarget    = $aCSIM;
        $this->legendcsimwintarget = $aCSIMWinTarget;
        $this->legendcsimalt       = $aCSIMAlt;
    }

    public function HideLegend($f = true): void
    {
        $this->hidelegend = $f;
    }

    public function SetPosition($aScalePosition): void
    {
        $this->scaleposition = $aScalePosition;
    }

    public function SetDirection($aDir): void
    {
        $this->direction = $aDir;
    }

    public function SetColor($aColor): void
    {
        $this->color = $aColor;
    }

    public function SetWeight($aWeight): void
    {
        $this->weight = $aWeight;
    }

    public function SetLineStyle($aStyle): void
    {
        $this->iLineStyle = $aStyle;
    }

    public function GetCSIMAreas()
    {
        return '';
    }

    //---------------
    // PRIVATE METHODS

    public function DoLegend($graph): void
    {
        if (!$this->hidelegend) {
            $this->Legend($graph);
        }
    }

    // Framework function the chance for each plot class to set a legend
    public function Legend($aGraph): void
    {
        if ('' != $this->legend) {
            $dummyPlotMark = new PlotMark();
            $lineStyle     = 1;
            $aGraph->legend->Add($this->legend, $this->color, $dummyPlotMark, $lineStyle, $this->legendcsimtarget, $this->legendcsimalt, $this->legendcsimwintarget);
        }
    }

    public function PreStrokeAdjust($aGraph): void
    {
        // Nothing to do
    }

    // Called by framework to allow the object to draw
    // optional information in the margin area
    public function StrokeMargin($aImg): void
    {
        // Nothing to do
    }

    // Framework function to allow the object to adjust the scale
    public function PrescaleSetup($aGraph): void
    {
        // Nothing to do
    }

    public function Min()
    {
        return [null, null];
    }

    public function Max()
    {
        return [null, null];
    }

    public function _Stroke($aImg, $aMinX, $aMinY, $aMaxX, $aMaxY, $aXPos, $aYPos): void
    {
        $aImg->SetColor($this->color);
        $aImg->SetLineWeight($this->weight);
        $oldStyle = $aImg->SetLineStyle($this->iLineStyle);
        if (VERTICAL == $this->direction) {
            $ymin_abs = $aMinY;
            $ymax_abs = $aMaxY;
            $xpos_abs = $aXPos;
            $aImg->StyleLine($xpos_abs, $ymin_abs, $xpos_abs, $ymax_abs);
        } elseif (HORIZONTAL == $this->direction) {
            $xmin_abs = $aMinX;
            $xmax_abs = $aMaxX;
            $ypos_abs = $aYPos;
            $aImg->StyleLine($xmin_abs, $ypos_abs, $xmax_abs, $ypos_abs);
        } else {
            JpGraphError::raiseL(25125); //(" Illegal direction for static line");
        }
        $aImg->SetLineStyle($oldStyle);
    }

    public function Stroke($aImg, $aXScale, $aYScale): void
    {
        $this->_Stroke($aImg, $aImg->left_margin, $aYScale->Translate($aYScale->GetMinVal()), $aImg->width - $aImg->right_margin, $aYScale->Translate($aYScale->GetMaxVal()), $aXScale->Translate($this->scaleposition), $aYScale->Translate($this->scaleposition));
    }
}
