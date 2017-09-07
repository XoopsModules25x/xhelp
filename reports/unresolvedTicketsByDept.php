<?php
//

require_once XHELP_JPGRAPH_PATH . '/jpgraph.php';
require_once XHELP_CLASS_PATH . '/report.php';
xhelpIncludeReportLangFile('unresolvedTicketsByDept');

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
 * Class XHelpUnresolvedTicketsByDeptReport
 */
class XHelpUnresolvedTicketsByDeptReport extends xhelpReport
{
    /**
     * XHelpUnresolvedTicketsByDeptReport constructor.
     */
    public function __construct()
    {
        $this->initVar('results', XOBJ_DTYPE_ARRAY, null, false);
        $this->initVar('hasResults', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('hasGraph', XOBJ_DTYPE_INT, 1, false);
    }

    public $name = 'unresolvedTicketsByDept';

    public $meta = [
        'name'        => _XHELP_UTBD_NAME,
        'author'      => 'Eric Juden',
        'authorEmail' => 'eric@3dev.org',
        'description' => _XHELP_UTBD_DESC,
        'version'     => '1.0',
        'dbFields'    => [
            'department'     => _XHELP_UTBD_DB1,
            'id'             => _XHELP_UTBD_DB2,
            'subject'        => _XHELP_UTBD_DB3,
            'status'         => _XHELP_UTBD_DB4,
            'totalTimeSpent' => _XHELP_UTBD_DB5,
            'postTime'       => _XHELP_UTBD_DB6
        ]
    ];

    public $parameters = [
        _XHELP_UTBD_PARAM1 => [
            'controltype' => XHELP_CONTROL_DATETIME,
            'fieldname'   => 'startDate',
            'value'       => '',      // last month
            'values'      => '',
            'fieldlength' => 25,
            'dbfield'     => 't.posted',
            'dbaction'    => '>'
        ],
        _XHELP_UTBD_PARAM2 => [
            'controltype' => XHELP_CONTROL_DATETIME,
            'fieldname'   => 'endDate',
            'value'       => '',      // today
            'values'      => '',
            'fieldlength' => 25,
            'dbfield'     => 't.posted',
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
     $myReport .= "<img src='".XHELP_BASE_URL."/report.php?op=graph&name=unresolvedTicketsByDept".$params."' align='center' width='500' height='300'>";
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

     $dept = '';
     foreach ($aResults as $result) {
     if ($result['department'] != $dept) {
     $myReport .= "<tr class='even'><td>".$result['department']."</td>";
     $dept = $result['department'];
     } else {
     $myReport .= "<tr class='even'><td></td>";
     }
     $myReport .= "<td><a href='".XHELP_BASE_URL."/ticket.php?id=".$result['id']."'>".$result['id']."</a></td>
     <td><a href='".XHELP_BASE_URL."/ticket.php?id=".$result['id']."'>".$result['subject']."</a></td>
     <td>".$result['status']."</td>
     <td>".$result['totalTimeSpent']."</td>
     <td>".$result['postTime']."</td></tr>";
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

        $i    = 0;
        $data = [];
        foreach ($aResults as $result) {
            if ($i > 0) {
                $ret = array_search($result['department'], $data[0]);
                if ($ret !== false) {
                    $data[1][$ret] += 1;
                } else {
                    $data[0][] = $result['department'];     // Used for identifier on chart
                    $data[1][] = 1;
                }
            } else {
                $data[0][] = $result['department'];
                $data[1][] = 1;
            }
            ++$i;
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
            'SELECT t.subject, d.department, s.description AS status, t.totalTimeSpent, t.posted, t.id, FROM_UNIXTIME(t.posted) AS postTime FROM %s d, %s t, %s u, %s s WHERE (d.id = t.department) AND (t.uid = u.uid) AND (t.status = s.id) AND (s.state = 1) %s',
                        $xoopsDB->prefix('xhelp_departments'),
            $xoopsDB->prefix('xhelp_tickets'),
            $xoopsDB->prefix('users'),
            $xoopsDB->prefix('xhelp_status'),
            $this->extraWhere
        );

        $result   = $xoopsDB->query($sSQL);
        $aResults = $this->_arrayFromData($result);
        $this->setVar('results', serialize($aResults));
        $this->setVar('hasResults', 1);

        return true;
    }
}
