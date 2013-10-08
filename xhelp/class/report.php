<?php
// $Id: report.php,v 1.6 2006/02/01 20:33:16 eric_juden Exp $

if (!defined('XHELP_CLASS_PATH')) {
    exit();
}

global $xoopsDB;


/**
 * xhelpReport class
 *
 * Information about an individual report
 *
 * @author Eric Juden <eric@3dev.org>
 * @access public
 * @package xhelp
 */
class xhelpReport extends XoopsObject {
    function xhelpReport()
    {
        parent::init();
        $this->initVar('results', XOBJ_DTYPE_ARRAY, null, false);
        $this->initVar('hasResults', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('hasGraph', XOBJ_DTYPE_INT, 0, false);
    }

    var $name = '';

    var $meta = array(
        'name' => '',
        'author' => '',
        'author_email' => '',
        'description' => '',
        'version' => '');

    var $parameters = array();
    var $extraWhere = '';

    /**
     * Generate a report
     *
     * @return string report
     * @access	public
     */
    function generateReport()
    {
        // Stub function - inherited by each /report/<reportfile>.php class
    }

    function generateReportNoData()
    {
        $myReport = '';
        $myReport .= "<div id='xhelp_report'>";
        $myReport .= "<table>";
        $myReport .= "<tr class='even'><td>". _XHELP_TEXT_NO_RECORDS ."</td></tr>";
        $myReport .= "</table>";
        $myReport .= "</div>";

        return $myReport;
    }

    /**
     * Generate graph to go along with report
     *
     * @return mixed bool false on no graph / draw graph
     * @access	public
     */
    function generateGraph()
    {
        if($this->getVar('hasGraph') == 0){
            return false;
        }
    }

    /**
     * Set SQL query to be run, and set results for class
     *
     * @return bool true on success / false on failure
     * @access	public
     */
    function _setResults()
    {
        // Stub function - inherited by each /report/<reportfile>.php class
    }

    /**
     * Returns an array from db query information
     *
     * @return array
     * @access	public
     */
    function _arrayFromData($dResult)
    {
        global $xoopsDB;

        $aResults = array();
        if(count($xoopsDB->getRowsNum($dResult) > 0)){      // Has data?

            $i = 0;
            $dbFields = $this->meta['dbFields'];
            while($myrow = $xoopsDB->fetchArray($dResult)){
                foreach($dbFields as $key=>$fieldname){
                    $aResults[$i][$key] = $myrow[$key];
                }
                $i++;
            }
        }
        return $aResults;
    }

    /**
     * Get meta information about the report
     *
     * @return array
     * @access	public
     */
    function getMeta()
    {
        return $this->meta;
    }

    /**
     * Get report parameters
     *
     * @return array {@link xhelpReportParameter} objects
     * @access	public
     */
    function getParams()
    {
        include_once(XHELP_CLASS_PATH .'/reportParameter.php');

        $params = array();
        foreach($this->parameters as $name=>$param){
            $params[] = xhelpReportParameter::addParam($param['controltype'], $name, $param['fieldname'], $param['value'], $param['values'], $param['fieldlength'], $param['dbfield'], $param['dbaction']);
        }
        return $params;
    }

    /**
     * Add additional items to where clause from report parameters for sql query string
     *
     * @return string $where (additional part of where clause)
     * @access	public
     */
    function makeWhere($params, $includeAnd = true)
    {
        $where = '';
        $i = 0;
        foreach($params as $param){
            if($param->value != '' && $param->value != -999){   // -999 used for all fields
                if($i == 0 && $includeAnd == true || $i > 0){
                    $where .= " AND ";
                }

                switch($param->dbaction){
                    case 'IN':
                        $where .= "(". $param->dbfield." IN (". ((is_array($param->value)) ? implode(array_values($param->value), ',') : $param->value) ."))";
                        break;

                    case '=':
                    default:
                        $where .= "(".$param->dbfield ." ". $param->dbaction ." '". $param->value ."')";
                        break;
                }
                $i++;
            }
        }
        return $where;
    }

    function generatePie3D($data, $legend_index = 0, $chartData_index = 1, $image = false, $length = 500, $width = 300, $hasShadow = true, $fontFamily = FF_FONT1, $fontStyle = FS_BOLD, $fontSize = '', $fontColor = 'black')
    {
        include_once(XHELP_JPGRAPH_PATH .'/jpgraph_pie.php');
        include_once(XHELP_JPGRAPH_PATH .'/jpgraph_pie3d.php');

        $graph = new PieGraph($length,$width);

        if($hasShadow){     // Add a shadow to the image
            $graph->setShadow();
        }

        $graph->title->Set($this->meta['name']);

        $p1 = new PiePlot3D($data[$chartData_index]);

        $p1->SetSize(.3);
        $p1->SetCenter(0.45);
        $p1->SetStartAngle(20);
        $p1->SetAngle(45);

        $p1->SetLegends($data[$legend_index]);

        $p1->value->SetFont($fontFamily,$fontStyle, $fontSize);
        $p1->value->SetColor($fontColor);
        $p1->SetLabelType(PIE_VALUE_PER);

        $a = array_search(max($data[$chartData_index]),$data[$chartData_index]); //Find the position of maximum value.
        $p1->ExplodeSlice($a);

        // Set graph background image
        if($image != false){
            $graph->SetBackgroundImage($image,BGIMG_FILLFRAME);
        }

        $graph->Add($p1);
        $graph->Stroke();
    }

    function generateStackedBarGraph($data, $legend_index = 0, $image = false, $aFillColors = array('red', 'green', 'orange', 'yellow', 'aqua', 'lime', 'teal', 'purple1', 'lightblue', 'blue'), $length = 500, $width = 300, $fontFamily = FF_FONT1, $fontStyle = FS_BOLD, $fontSize = '', $fontColor = 'black', $marginColor = 'white')
    {
        include_once(XHELP_JPGRAPH_PATH .'/jpgraph_bar.php');

        $graph = new Graph($length,$width);
        $graph->title->Set($this->meta['name']);
        $graph->SetScale("textint");
        $graph->yaxis->scale->SetGrace(30);

        //$graph->ygrid->Show(true,true);
        $graph->ygrid->SetColor('gray','lightgray@0.5');


        // Setup graph colors
        $graph->SetMarginColor($marginColor);
        $datazero=array(0,0,0,0);

        // Create the "dummy" 0 bplot
        $bplotzero = new BarPlot($datazero);

        // Set names as x-axis label
        $graph->xaxis->SetTickLabels($data[$legend_index]);

        // for loop through data array starting with element 1
        $aPlots = array();
        for($i=1; $i < count($data); $i++){
            $ybplot1 = new BarPlot($data[$i]);
            $ybplot1->setFillColor($aFillColors[$i]);
            $ybplot1->value->Show();
            $ybplot1->value->SetFont($fontFamily,$fontStyle, $fontSize);
            $ybplot1->value->SetColor($fontColor);

            $aPlots[] = $ybplot1;
        }
        //$ybplot = new AccBarPlot(array($ybplot1,$bplotzero));
        $ybplot = new AccBarPlot($aPlots, $bplotzero);
        $graph->Add($ybplot);

        // Set graph background image
        $graph->SetBackgroundImage($image,BGIMG_FILLFRAME);

        $graph->Stroke();
    }
}
?>