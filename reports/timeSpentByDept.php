<?php
//

require_once XHELP_JPGRAPH_PATH . '/jpgraph.php';
require_once XHELP_CLASS_PATH . '/report.php';
xhelpIncludeReportLangFile('timeSpentByDept');

global $xoopsDB, $paramVals;

$startDate = date('m/d/y h:i:s A', mktime(0, 0, 0, date('m') - 1, date('d'), date('Y')));
$endDate   = date('m/d/y') . ' 12:00:00 AM';

// Cannot fill date values in class...have to fill these values later
$paramVals = [
    'startDate' => (isset($_REQUEST['startDate'])
                    && $_REQUEST['startDate'] != '') ? $_REQUEST['startDate'] : $startDate,
    'endDate'   => (isset($_REQUEST['endDate']) && $_REQUEST['endDate'] != '') ? $_REQUEST['endDate'] : $endDate
];

/**
 * Class XHelpTimeSpentByDeptReport
 */
class XHelpTimeSpentByDeptReport extends xhelpReport
{
    /**
     * XHelpTimeSpentByDeptReport constructor.
     */
    public function __construct()
    {
        $this->initVar('results', XOBJ_DTYPE_ARRAY, null, false);
        $this->initVar('hasResults', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('hasGraph', XOBJ_DTYPE_INT, 1, false);
    }

    public $name = 'timeSpentByDept';

    public $meta = [
        'name'        => _XHELP_TSBD_NAME,
        'author'      => 'Eric Juden',
        'authorEmail' => 'eric@3dev.org',
        'description' => _XHELP_TSBD_DESC,
        'version'     => '1.0',
        'dbFields'    => [
            'department' => _XHELP_TSBD_DB1,
            'TotalTime'  => _XHELP_TSBD_DB2
        ]
    ];

    public $parameters = [
        _XHELP_TSBD_PARAM1 => [
            'controltype' => XHELP_CONTROL_DATETIME,
            'fieldname'   => 'startDate',
            'value'       => '',      // last month
            'values'      => '',
            'fieldlength' => 25,
            'dbfield'     => 'r.updateTime',
            'dbaction'    => '>'
        ],
        _XHELP_TSBD_PARAM2 => [
            'controltype' => XHELP_CONTROL_DATETIME,
            'fieldname'   => 'endDate',
            'value'       => '',      // today
            'values'      => '',
            'fieldlength' => 25,
            'dbfield'     => 'r.updateTime',
            'dbaction'    => '<='
        ]
    ];

    /*
     function generateReport()
     {
     global $paramVals;

     if ($this->getVar('hasResults') == 0) {
     $this->_setResults();
     }
     $aResults = $this->getVar('results');

     if (empty($aResults)) {       // If no records found
     $myReport = $this->generateReportNoData();

     return $myReport;
     }

     $params = '';
     foreach ($paramVals as $key=>$value) {
     $params .= "&$key=$value";
     }

     // Print graph
     $myReport = '';
     $myReport .= "<div id='xhelp_graph'>";
     $myReport .= "<img src='".XHELP_BASE_URL."/report.php?op=graph&name=timeSpentByDept".$params."' align='center' width='500' height='300'>";
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

     $totalTime = 0;
     foreach ($aResults as $result) {
     $myReport .= "<tr class='even'>";

     foreach ($dbFields as $dbField=>$field) {
     $myReport .= "<td>". $result[$dbField] ."</td>";
     if ($dbField == 'TotalTime') {
     $totalTime += $result[$dbField];
     }
     }
     $myReport .= "</tr>";
     }

     // Display total time
     $myReport .= "<tr class='foot'><td>"._XHELP_TEXT_TOTAL."</td><td>". $totalTime ."</td></tr>";

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

        $i    = 0;
        $data = [];
        foreach ($aResults as $result) {
            $data[0][] = $result['department'];     // Used for identifier on chart
            $data[1][] = $result['TotalTime'];      // used for data on chart
        }

        $this->generatePie3D($data, 0, 1, XHELP_IMAGE_PATH . '/graph_bg.jpg');
    }

    /**
     * @return bool
     */
    public function _setResults()
    {
        global $xoopsDB;

        $sSQL = sprintf(
            'SELECT d.department, SUM(r.timeSpent) AS TotalTime FROM %s d, %s t, %s r WHERE (d.id = t.department) AND (t.id = r.ticketid) %s GROUP BY d.department',
            $xoopsDB->prefix('xhelp_departments'),
            $xoopsDB->prefix('xhelp_tickets'),
            $xoopsDB->prefix('xhelp_responses'),
                        $this->extraWhere
        );

        $result   = $xoopsDB->query($sSQL);
        $aResults = $this->_arrayFromData($result);
        $this->setVar('results', serialize($aResults));
        $this->setVar('hasResults', 1);

        return true;
    }
}
