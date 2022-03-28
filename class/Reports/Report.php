<?php declare(strict_types=1);

namespace XoopsModules\Xhelp\Reports;

/*
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * @copyright    {@link https://xoops.org/ XOOPS Project}
 * @license      {@link https://www.gnu.org/licenses/gpl-2.0.html GNU GPL 2 or later}
 * @author       Eric Juden <ericj@epcusa.com>
 * @author       XOOPS Development Team
 */

use XoopsModules\Xhelp;
use Amenadiel\JpGraph;
use Amenadiel\JpGraph\Plot;
use Amenadiel\JpGraph\Graph;
use Amenadiel\JpGraph\Text;
use Amenadiel\JpGraph\Util;

require \dirname(__DIR__, 2) . '/vendor/amenadiel/jpgraph/src/config.inc.php';

if (!\defined('XHELP_CLASS_PATH')) {
    exit();
}

global $xoopsDB;

/**
 * Report class
 *
 * Information about an individual report
 *
 * @author  Eric Juden <eric@3dev.org>
 */
class Report extends \XoopsObject
{
    /**
     * Report constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->initVar('results', \XOBJ_DTYPE_ARRAY, null, false);
        $this->initVar('hasResults', \XOBJ_DTYPE_INT, 0, false);
        $this->initVar('hasGraph', \XOBJ_DTYPE_INT, 0, false);
    }

    public $name       = 'Report';
    public $meta       = [
        'name'         => '',
        'author'       => '',
        'author_email' => '',
        'description'  => '',
        'version'      => '',
    ];
    public $parameters = [];
    public $extraWhere = '';

    /**
     * Generate a report
     *
     * @return string report
     */
    public function generateReport(): string
    {
        // Stub function - inherited by each /report/<reportfile>.php class
        return '';
    }

    /**
     * @return string
     */
    public function generateReportNoData(): string
    {
        $myReport = '';
        $myReport .= "<div id='xhelp_report'>";
        $myReport .= '<table>';
        $myReport .= "<tr class='even'><td>" . \_XHELP_TEXT_NO_RECORDS . '</td></tr>';
        $myReport .= '</table>';
        $myReport .= '</div>';

        return $myReport;
    }

    /**
     * Generate graph to go along with report
     *
     * @return false|void bool false on no graph / draw graph
     */
    public function generateGraph()
    {
        if (0 == $this->getVar('hasGraph')) {
            return false;
        }
    }

    /**
     * Set SQL query to be run, and set results for class
     */
    public function setResults()
    {
        // Stub function - inherited by each /report/<reportfile>.php class
    }

    /**
     * Returns an array from db query information
     *
     * @param mysqli_result $dResult
     * @return array
     */
    public function arrayFromData(mysqli_result $dResult): array
    {
        global $xoopsDB;

        $aResults = [];
        if (($xoopsDB->getRowsNum($dResult)) > 0) {      // Has data?
            $i        = 0;
            $dbFields = $this->meta['dbFields'];
            while (false !== ($myrow = $xoopsDB->fetchArray($dResult))) {
                foreach ($dbFields as $key => $fieldname) {
                    $aResults[$i][$key] = $myrow[$key];
                }
                ++$i;
            }
        }

        return $aResults;
    }

    /**
     * Get meta information about the report
     *
     * @return array
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * Get report parameters
     *
     * @return array {@link Xhelp\ReportParameter} objects
     */
    public function getParams(): array
    {
        // require_once XHELP_CLASS_PATH . '/ReportParameter.php';

        $params = [];
        foreach ($this->parameters as $name => $param) {
            $params[] = Xhelp\ReportParameter::addParam($param['controltype'], $name, $param['fieldname'], $param['value'], (array)$param['values'], $param['fieldlength'], $param['dbfield'], $param['dbaction']);
        }

        return $params;
    }

    /**
     * Add additional items to where clause from report parameters for sql query string
     *
     * @param array $params
     * @param bool  $includeAnd
     * @return string (additional part of where clause)
     */
    public function makeWhere(array $params, bool $includeAnd = true): string
    {
        $where = '';
        $i     = 0;
        foreach ($params as $param) {
            if ('' != $param->value && -999 != $param->value) {   // -999 used for all fields
                if (0 == $i && true === $includeAnd || $i > 0) {
                    $where .= ' AND ';
                }

                switch ($param->dbaction) {
                    case 'IN':
                        $where .= '(' . $param->dbfield . ' IN (' . (\is_array($param->value) ? \implode(',', \array_values($param->value)) : $param->value) . '))';
                        break;
                    case '=':
                    default:
                        $where .= '(' . $param->dbfield . ' ' . $param->dbaction . " '" . $param->value . "')";
                        break;
                }
                ++$i;
            }
        }

        return $where;
    }

    /**
     * @param array  $data
     * @param int    $legend_index
     * @param int    $chartData_index
     * @param bool   $image
     * @param int    $length
     * @param int    $width
     * @param bool   $hasShadow
     * @param int    $fontFamily
     * @param int    $fontStyle
     * @param string $fontSize
     * @param string $fontColor
     */
    public function generatePie3D(
        array $data, int $legend_index = 0, int $chartData_index = 1, bool $image = false, int $length = 500, int $width = 300, bool $hasShadow = true, $fontFamily = FF_FONT1, $fontStyle = FS_BOLD, string $fontSize = '', string $fontColor = 'black'
    ): void {
        //        require_once \XHELP_JPGRAPH_PATH . '/jpgraph_pie.php';
        //        require_once \XHELP_JPGRAPH_PATH . '/jpgraph_pie3d.php';

        $graph = new Graph\PieGraph($length, $width);

        if ($hasShadow) {     // Add a shadow to the image
            $graph->setShadow();
        }

        $graph->title->Set($this->meta['name']);

        $p1 = new Plot\PiePlot3D($data[$chartData_index]);

        $p1->SetSize(.3);
        $p1->SetCenter(0.45);
        $p1->SetStartAngle(20);
        $p1->SetAngle(45);

        $p1->SetLegends($data[$legend_index]);

        $p1->value->SetFont($fontFamily, $fontStyle, $fontSize);
        $p1->value->SetColor($fontColor);
        $p1->SetLabelType(PIE_VALUE_PER);

        $a = \array_search(\max($data[$chartData_index]), $data[$chartData_index]); //Find the position of maximum value.
        $p1->ExplodeSlice($a);

        // Set graph background image
        if ($image) {
            $graph->SetBackgroundImage($image, BGIMG_FILLFRAME);
        }

        $graph->Add($p1);
        $graph->Stroke();
    }

    /**
     * @param array       $data
     * @param int         $legend_index
     * @param bool|string $image
     * @param array       $aFillColors
     * @param int         $length
     * @param int         $width
     * @param int         $fontFamily
     * @param int         $fontStyle
     * @param string      $fontSize
     * @param string      $fontColor
     * @param string      $marginColor
     */
    public function generateStackedBarGraph(
        array $data, int $legend_index = 0, $image = false, array $aFillColors = [
        'red',
        'green',
        'orange',
        'yellow',
        'aqua',
        'lime',
        'teal',
        'purple1',
        'lightblue',
        'blue',
    ], int    $length = 500, int $width = 300, $fontFamily = FF_FONT1, $fontStyle = FS_BOLD, string $fontSize = '', string $fontColor = 'black', string $marginColor = 'white'
    ): void {
        //        require_once \XHELP_JPGRAPH_PATH . '/jpgraph_bar.php';

        $graph = new Graph\Graph($length, $width);
        $graph->title->Set($this->meta['name']);
        $graph->setScale('textint');
        $graph->yaxis->scale->SetGrace(30);

        //$graph->ygrid->Show(true,true);
        $graph->ygrid->SetColor('gray', 'lightgray@0.5');

        // Setup graph colors
        $graph->SetMarginColor($marginColor);
        $datazero = [0, 0, 0, 0];

        // Create the "dummy" 0 bplot
        $bplotzero = new Plot\BarPlot($datazero);

        // Set names as x-axis label
        $graph->xaxis->SetTickLabels($data[$legend_index]);

        // for loop through data array starting with element 1
        $aPlots = [];
        for ($i = 1, $iMax = \count($data); $i < $iMax; ++$i) {
            $ybplot1 = new Plot\BarPlot($data[$i]);
            $ybplot1->setFillColor($aFillColors[$i]);
            $ybplot1->value->Show();
            $ybplot1->value->SetFont($fontFamily, $fontStyle, $fontSize);
            $ybplot1->value->SetColor($fontColor);

            $aPlots[] = $ybplot1;
        }
        //$ybplot = new AccBarPlot(array($ybplot1,$bplotzero));
        $ybplot = new Plot\AccBarPlot($aPlots, $bplotzero);
        $graph->Add($ybplot);

        // Set graph background image
        $graph->SetBackgroundImage($image, BGIMG_FILLFRAME);

        $graph->Stroke();
    }
}
