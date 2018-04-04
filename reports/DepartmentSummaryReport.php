<?php

use XoopsModules\Xhelp;

require_once XHELP_JPGRAPH_PATH . '/jpgraph.php';
require_once XHELP_JPGRAPH_PATH . '/jpgraph_bar.php';// añado para ver si funciona <====================18/03/2010
// require_once XHELP_CLASS_PATH . '/report.php';
Xhelp\Utility::includeReportLangFile('departmentSummary');

global $xoopsDB, $paramVals;

$startDate = date('m/d/y h:i:s A', mktime(0, 0, 0, date('m') - 1, date('d'), date('Y')));
$endDate   = date('m/d/y') . ' 12:00:00 AM';

$hDepartments = new Xhelp\DepartmentHandler($GLOBALS['xoopsDB']);
$crit         = new \Criteria('', '');
$crit->setSort('department');
$crit->setOrder('ASC');
$departments = $hDepartments->getObjects($crit, true);

$i            = 0;
$aDepts       = [];
$aDepts[-999] = 'All';
foreach ($departments as $id => $dept) {
    if (0 == $i) {
        $deptid = $id;
    }
    $aDepts[$id] = $dept->getVar('department');
    ++$i;
}

// Cannot fill date values in class...have to fill these values later
$paramVals = [
    'startDate'  => (isset($_REQUEST['startDate'])
                     && '' != $_REQUEST['startDate']) ? $_REQUEST['startDate'] : $startDate,
    'endDate'    => (isset($_REQUEST['endDate']) && '' != $_REQUEST['endDate']) ? $_REQUEST['endDate'] : $endDate,
    'department' => [$aDepts, isset($_REQUEST['department']) ? $_REQUEST['department'] : '']
];

/**
 * class DepartmentSummaryReport
 */
class DepartmentSummaryReport extends Xhelp\Report
{
    /**
     * Xhelp\DepartmentSummaryReport constructor.
     */
    public function __construct()
    {
        $this->initVar('results', XOBJ_DTYPE_ARRAY, null, false);
        $this->initVar('hasResults', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('hasGraph', XOBJ_DTYPE_INT, 1, false);
    }

    public $name = 'departmentSummary';
    public $meta = [
        'name'        => _XHELP_DS_NAME,
        'author'      => 'Eric Juden',
        'authorEmail' => 'eric@3dev.org',
        'description' => _XHELP_DS_DESC,
        'version'     => '1.0',
        'dbFields'    => [
            'department'        => _XHELP_DS_DB1,
            'totalTimeSpent'    => _XHELP_DS_DB2,
            'resolvedTickets'   => _XHELP_DS_DB3,
            'unresolvedTickets' => _XHELP_DS_DB4,
        ]
    ];

    public $parameters = [
        _XHELP_DS_PARAM1 => [
            'controltype' => XHELP_CONTROL_DATETIME,
            'fieldname'   => 'startDate',
            'value'       => '',      // last month
            'values'      => '',
            'fieldlength' => 25,
            'dbfield'     => 't.posted',
            'dbaction'    => '>'
        ],
        _XHELP_DS_PARAM2 => [
            'controltype' => XHELP_CONTROL_DATETIME,
            'fieldname'   => 'endDate',
            'value'       => '',      // today
            'values'      => '',
            'fieldlength' => 25,
            'dbfield'     => 't.posted',
            'dbaction'    => '<='
        ],
        _XHELP_DS_PARAM3 => [
            'controltype' => XHELP_CONTROL_SELECT,
            'fieldname'   => 'department',
            'value'       => '',
            'values'      => [],
            'fieldlength' => 25,
            'dbfield'     => 'd.id',
            'dbaction'    => 'IN'
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
     if ($key == 'department') {
     if (isset($_REQUEST['department'])) {
     $params .= "&$key=".$_REQUEST['department'];
     }
     } else {
     $params .= "&$key=$value";
     }
     }

     // Print graph
     $myReport = '';
     $myReport .= "<div id='xhelp_graph'>";
     $myReport .= "<img src='".XHELP_BASE_URL."/report.php?op=graph&name=departmentSummary".$params."' align='center' width='500' height='300'>";
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

     $totalTimeSpent = 0;
     $totalResolved = 0;
     $totalUnresolved = 0;
     foreach ($aResults as $result) {
     $myReport .= "<tr class='even'>";

     foreach ($dbFields as $dbField=>$field) {
     $myReport .= "<td>". $result[$dbField] ."</td>";
     if ($dbField == 'totalTimeSpent') {
     $totalTimeSpent += $result[$dbField];
     } elseif ($dbField == 'resolvedTickets') {
     $totalResolved += $result[$dbField];
     } elseif ($dbField == 'unresolvedTickets') {
     $totalUnresolved += $result[$dbField];
     }
     }
     $myReport .= "</tr>";
     }

     // Display total time
     $myReport .= "<tr class='foot'>
     <td>"._XHELP_TEXT_TOTAL."</td>
     <td>". $totalTimeSpent ."</td>
     <td>". $totalResolved ."</td>
     <td>". $totalUnresolved ."</td>
     </tr>";

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
        if (0 == $this->getVar('hasGraph')) {
            return false;
        }

        if (0 == $this->getVar('hasResults')) {
            $this->_setResults();
        }
        $aResults = $this->getVar('results');

        $data = [];
        foreach ($aResults as $result) {
            $data[0][] = $result['department'];
            $data[1][] = $result['resolvedTickets'];
            $data[2][] = $result['unresolvedTickets'];
        }

        $this->generateStackedBarGraph($data, 0, XHELP_IMAGE_PATH . '/graph_bg.jpg', ['red', 'green', 'orange']);
    }

    /**
     * @return bool
     */
    public function _setResults()
    {
        global $xoopsDB;

        $sSQL = sprintf(
            'SELECT d.id, d.department, SUM(t.totalTimeSpent) AS totalTimeSpent, COUNT(*) AS resolvedTickets, 0 AS unresolvedTickets, 0 AS avgResponseTime FROM `%s` d, %s t INNER JOIN %s st ON st.id = t.status WHERE st.state = 2 AND (t.department = d.id) %s GROUP BY d.department',
                        $xoopsDB->prefix('xhelp_departments'),
            $xoopsDB->prefix('xhelp_tickets'),
            $xoopsDB->prefix('xhelp_status'),
            $this->extraWhere
        );

        $sSQL2 = sprintf(
            'SELECT d.id, d.department, SUM(t.totalTimeSpent) AS totalTimeSpent, 0 AS resolvedTickets, COUNT(*) AS unresolvedTickets, 0 AS avgResponseTime FROM `%s` d, %s t INNER JOIN %s st ON st.id = t.status WHERE st.state = 1 AND (t.department = d.id) %s GROUP BY d.department',
                         $xoopsDB->prefix('xhelp_departments'),
            $xoopsDB->prefix('xhelp_tickets'),
            $xoopsDB->prefix('xhelp_status'),
            $this->extraWhere
        );

        $result  = $xoopsDB->queryF($sSQL);
        $result2 = $xoopsDB->queryF($sSQL2);

        $aResults = $this->_arrayFromData([$result, $result2]);

        $this->setVar('results', serialize($aResults));
        $this->setVar('hasResults', 1);

        return true;
    }

    /**
     * @param $dResult
     * @return array
     */
    public function _arrayFromData($dResult)
    {
        global $xoopsDB;

        $aResults = [];

        foreach ($dResult as $dRes) {
            if (count($xoopsDB->getRowsNum($dRes) > 0)) {      // Has data?
                $i        = 0;
                $dbFields = $this->meta['dbFields'];
                while (false !== ($myrow = $xoopsDB->fetchArray($dRes))) {    // Loop through each db record
                    foreach ($dbFields as $key => $fieldname) {     // Loop through each dbfield for report
                        if (!isset($myrow[$key]) || null === $myrow[$key]) {
                            $aResults[$myrow['department']][$key] = 0;
                        } elseif (is_numeric($myrow[$key])) {
                            if (isset($aResults[$myrow['department']][$key])) {
                                $aResults[$myrow['department']][$key] += $myrow[$key];
                            } else {
                                $aResults[$myrow['department']][$key] = $myrow[$key];
                            }
                        } else {
                            $aResults[$myrow['department']][$key] = $myrow[$key];
                        }
                    }
                    ++$i;
                }
            }
        }

        return $aResults;
    }
}
