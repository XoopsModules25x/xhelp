<?php namespace XoopsModules\Xhelp;

use XoopsModules\Xhelp;

if (!defined('XHELP_CLASS_PATH')) {
    exit();
}

global $xoopsDB;

/**
 * Xhelp\Report class
 *
 * Information about an individual report
 *
 * @author  Eric Juden <eric@3dev.org>
 * @access  public
 * @package xhelp
 */
class Report extends \XoopsObject
{
    /**
     * Xhelp\Report constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->initVar('results', XOBJ_DTYPE_ARRAY, null, false);
        $this->initVar('hasResults', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('hasGraph', XOBJ_DTYPE_INT, 0, false);
    }

    public $name = '';

    public $meta = [
        'name'         => '',
        'author'       => '',
        'author_email' => '',
        'description'  => '',
        'version'      => ''
    ];

    public $parameters = [];
    public $extraWhere = '';

    /**
     * Generate a report
     *
     * @return string report
     * @access  public
     */
    public function generateReport()
    {
        // Stub function - inherited by each /report/<reportfile>.php class
    }

    /**
     * @return string
     */
    public function generateReportNoData()
    {
        $myReport = '';
        $myReport .= "<div id='xhelp_report'>";
        $myReport .= '<table>';
        $myReport .= "<tr class='even'><td>" . _XHELP_TEXT_NO_RECORDS . '</td></tr>';
        $myReport .= '</table>';
        $myReport .= '</div>';

        return $myReport;
    }

    /**
     * Generate graph to go along with report
     *
     * @return mixed bool false on no graph / draw graph
     * @access  public
     */
    public function generateGraph()
    {
        if (0 == $this->getVar('hasGraph')) {
            return false;
        }
    }

    /**
     * Set SQL query to be run, and set results for class
     *
     * @return void true on success / false on failure
     * @access  public
     */
    public function _setResults()
    {
        // Stub function - inherited by each /report/<reportfile>.php class
    }

    /**
     * Returns an array from db query information
     *
     * @param $dResult
     * @return array
     * @access  public
     */
    public function _arrayFromData($dResult)
    {
        global $xoopsDB;

        $aResults = [];
        if (count($xoopsDB->getRowsNum($dResult)) > 0) {      // Has data?

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
     * @access  public
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * Get report parameters
     *
     * @return array {@link Xhelp\ReportParameter} objects
     * @access  public
     */
    public function getParams()
    {
        // require_once XHELP_CLASS_PATH . '/ReportParameter.php';

        $params = [];
        foreach ($this->parameters as $name => $param) {
            $params[] = Xhelp\ReportParameter::addParam($param['controltype'], $name, $param['fieldname'], $param['value'], $param['values'], $param['fieldlength'], $param['dbfield'], $param['dbaction']);
        }

        return $params;
    }

    /**
     * Add additional items to where clause from report parameters for sql query string
     *
     * @param      $params
     * @param bool $includeAnd
     * @return string $where (additional part of where clause)
     * @access  public
     */
    public function makeWhere($params, $includeAnd = true)
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
                        $where .= '(' . $param->dbfield . ' IN (' . (is_array($param->value) ? implode(array_values($param->value), ',') : $param->value) . '))';
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
     * @param        $data
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
        $data,
        $legend_index = 0,
        $chartData_index = 1,
        $image = false,
        $length = 500,
        $width = 300,
        $hasShadow = true,
        $fontFamily = FF_FONT1,
        $fontStyle = FS_BOLD,
        $fontSize = '',
        $fontColor = 'black'
    ) {
        require_once XHELP_JPGRAPH_PATH . '/jpgraph_pie.php';
        require_once XHELP_JPGRAPH_PATH . '/jpgraph_pie3d.php';

        $graph = new PieGraph($length, $width);

        if ($hasShadow) {     // Add a shadow to the image
            $graph->setShadow();
        }

        $graph->title->Set($this->meta['name']);

        $p1 = new PiePlot3D($data[$chartData_index]);

        $p1->SetSize(.3);
        $p1->SetCenter(0.45);
        $p1->SetStartAngle(20);
        $p1->SetAngle(45);

        $p1->SetLegends($data[$legend_index]);

        $p1->value->SetFont($fontFamily, $fontStyle, $fontSize);
        $p1->value->SetColor($fontColor);
        $p1->SetLabelType(PIE_VALUE_PER);

        $a = array_search(max($data[$chartData_index]), $data[$chartData_index]); //Find the position of maximum value.
        $p1->ExplodeSlice($a);

        // Set graph background image
        if (false !== $image) {
            $graph->SetBackgroundImage($image, BGIMG_FILLFRAME);
        }

        $graph->Add($p1);
        $graph->Stroke();
    }

    /**
     * @param        $data
     * @param int    $legend_index
     * @param bool   $image
     * @param array  $aFillColors
     * @param int    $length
     * @param int    $width
     * @param int    $fontFamily
     * @param int    $fontStyle
     * @param string $fontSize
     * @param string $fontColor
     * @param string $marginColor
     */
    public function generateStackedBarGraph(
        $data,
        $legend_index = 0,
        $image = false,
        $aFillColors = [
            'red',
            'green',
            'orange',
            'yellow',
            'aqua',
            'lime',
            'teal',
            'purple1',
            'lightblue',
            'blue'
        ],
        $length = 500,
        $width = 300,
        $fontFamily = FF_FONT1,
        $fontStyle = FS_BOLD,
        $fontSize = '',
        $fontColor = 'black',
        $marginColor = 'white'
    ) {
        require_once XHELP_JPGRAPH_PATH . '/jpgraph_bar.php';

        $graph = new Graph($length, $width);
        $graph->title->Set($this->meta['name']);
        $graph->SetScale('textint');
        $graph->yaxis->scale->SetGrace(30);

        //$graph->ygrid->Show(true,true);
        $graph->ygrid->SetColor('gray', 'lightgray@0.5');

        // Setup graph colors
        $graph->SetMarginColor($marginColor);
        $datazero = [0, 0, 0, 0];

        // Create the "dummy" 0 bplot
        $bplotzero = new BarPlot($datazero);

        // Set names as x-axis label
        $graph->xaxis->SetTickLabels($data[$legend_index]);

        // for loop through data array starting with element 1
        $aPlots = [];
        for ($i = 1, $iMax = count($data); $i < $iMax; ++$i) {
            $ybplot1 = new BarPlot($data[$i]);
            $ybplot1->setFillColor($aFillColors[$i]);
            $ybplot1->value->Show();
            $ybplot1->value->SetFont($fontFamily, $fontStyle, $fontSize);
            $ybplot1->value->SetColor($fontColor);

            $aPlots[] = $ybplot1;
        }
        //$ybplot = new AccBarPlot(array($ybplot1,$bplotzero));
        $ybplot = new AccBarPlot($aPlots, $bplotzero);
        $graph->Add($ybplot);

        // Set graph background image
        $graph->SetBackgroundImage($image, BGIMG_FILLFRAME);

        $graph->Stroke();
    }
}
