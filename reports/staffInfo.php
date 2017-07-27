<?php
//

require_once XHELP_JPGRAPH_PATH . '/jpgraph.php';
require_once XHELP_JPGRAPH_PATH . '/jpgraph_bar.php';
require_once XHELP_CLASS_PATH . '/report.php';
xhelpIncludeReportLangFile('staffInfo');

global $xoopsDB;

/**
 * Class XHelpStaffInfoReport
 */
class XHelpStaffInfoReport extends xhelpReport
{
    /**
     * XHelpStaffInfoReport constructor.
     */
    public function __construct()
    {
        $this->initVar('results', XOBJ_DTYPE_ARRAY, null, false);
        $this->initVar('hasResults', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('hasGraph', XOBJ_DTYPE_INT, 1, false);

        /*
         if (PHP_VERSION >= 5) {      // Problems with JPGRAPH and php5 using bar graphs - Don't display for php5
         $this->setVar('hasGraph', 0);
         } else {
         $this->setVar('hasGraph', 1);
         }
         */
    }

    public $name = 'staffInfo';

    public $meta = [
        'name'        => _XHELP_STAFF_INFO_NAME,
        'author'      => 'Eric Juden',
        'authorEmail' => 'eric@3dev.org',
        'description' => _XHELP_STAFF_INFO_DESC,
        'version'     => '1.0',
        'dbFields'    => [
            'name'             => 'Name',
            'ticketsResponded' => 'Tickets Responded',
            'callsClosed'      => 'Calls Closed',
            'avgResponseTime'  => 'Average Response Time (in Minutes)'
        ]
    ];

    public $parameters = [];

    /*
     function generateReport()
     {
     if ($this->getVar('hasResults') == 0) {
     $this->_setResults();
     }
     $aResults = $this->getVar('results');

     if (empty($aResults)) {       // If no records found
     $myReport = $this->generateReportNoData();

     return $myReport;
     }

     // Print graph
     $myReport = '';
     $myReport .= "<div id='xhelp_graph'>";
     $myReport .= "<img src='".XHELP_BASE_URL."/report.php?op=graph&name=staffInfo' align='center' width='500' height='300'>";
     $myReport .= "</div>";

     // Display report
     $myReport .= "<br>";
     $myReport .= "<div id='xhelp_report'>";
     $myReport .= "<table>";
     $myReport .= "<tr>";
     $dbFields = $this->meta['dbFields'];
     foreach ($dbFields as $dbField=>$field) {
     $myReport .= "<th>".$field."</th>";
     }
     $myReport .= "</tr>";

     foreach ($aResults as $result) {
     $myReport .= "<tr class='even'>";
     foreach ($dbFields as $dbField=>$field) {
     $myReport .= "<td>". $result[$dbField] ."</td>";
     }
     $myReport .= "</tr>";
     }
     $myReport .= "</table>";
     $myReport .= "</div>";

     return $myReport;
     }
     */

    /**
     * @return bool
     */
    public function generateGraph()
    {
        if ($this->getVar('hasGraph') == 0) {
            return false;
        }

        if ($this->getVar('hasResults') == 0) {
            $this->_setResults();
        }
        $aResults = $this->getVar('results');

        $graph = new Graph(500, 300);
        $graph->title->Set($this->meta['name']);
        $graph->SetScale('textint');
        $graph->yaxis->scale->SetGrace(30);

        //$graph->ygrid->Show(true,true);
        $graph->ygrid->SetColor('gray', 'lightgray@0.5');

        // Setup graph colors
        $graph->SetMarginColor('white');

        $i    = 0;
        $data = [];
        foreach ($aResults as $result) {
            $data[0][] = $result['name'];
            $data[1][] = $result['ticketsResponded'];
            $data[2][] = $result['callsClosed'];
            $data[3][] = $result['avgResponseTime'];
        }

        $datazero = [0, 0, 0, 0];

        // Create the "dummy" 0 bplot
        $bplotzero = new BarPlot($datazero);

        // Set names as x-axis label
        $graph->xaxis->SetTickLabels($data[0]);

        // Create the "Y" axis group
        foreach ($data as $d) {
            $ybplot1 = new BarPlot($d);
            $ybplot1->value->Show();
            $ybplot = new GroupBarPlot([$ybplot1, $bplotzero]);

            $graph->Add($ybplot);
        }

        // Set graph background image
        $graph->SetBackgroundImage(XHELP_IMAGE_PATH . '/graph_bg.jpg', BGIMG_FILLFRAME);

        $graph->Stroke();
    }

    /**
     * @return bool
     */
    public function _setResults()
    {
        global $xoopsDB;
        $sSQL = sprintf('SELECT DISTINCT s.ticketsResponded, s.callsClosed, s.email, u.name, s.responseTime / s.ticketsResponded / 60 AS avgResponseTime FROM %s s, %s u, %s t WHERE (s.uid = u.uid) AND (s.uid = t.ownership) AND (s.uid = t.closedBy) %s', $xoopsDB->prefix('xhelp_staff'),
                        $xoopsDB->prefix('users'), $xoopsDB->prefix('xhelp_tickets'), $this->extraWhere);

        $result   = $xoopsDB->query($sSQL);
        $aResults = $this->_arrayFromData($result);

        $this->setVar('results', serialize($aResults));
        $this->setVar('hasResults', 1);

        return true;
    }

    /**
     *
     */
    public function getParams()
    {
    }
}
