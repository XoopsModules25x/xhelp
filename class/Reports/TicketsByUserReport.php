<?php declare(strict_types=1);

namespace XoopsModules\Xhelp\Reports;

use XoopsModules\Xhelp;

require_once \XHELP_JPGRAPH_PATH . '/jpgraph.php';
// require_once XHELP_CLASS_PATH . '/report.php';
Xhelp\Utility::includeReportLangFile('ticketsByUser');

global $xoopsDB, $paramVals;

$startDate = \date('m/d/y h:i:s A', \mktime(0, 0, 0, \date('m') - 1, \date('d'), \date('Y')));
$endDate   = \date('m/d/y') . ' 12:00:00 AM';

// Cannot fill date values in class...have to fill these values later
$paramVals = [
    'startDate' => (isset($_REQUEST['startDate'])
                    && '' != $_REQUEST['startDate']) ? $_REQUEST['startDate'] : $startDate,
    'endDate'   => (isset($_REQUEST['endDate']) && '' != $_REQUEST['endDate']) ? $_REQUEST['endDate'] : $endDate,
];

/**
 * class TicketsByUserReport
 */
class TicketsByUserReport extends Xhelp\Reports\Report
{
    /**
     * Xhelp\TicketsByUserReport constructor.
     */
    public function __construct()
    {
        $this->initVar('results', \XOBJ_DTYPE_ARRAY, null, false);
        $this->initVar('hasResults', \XOBJ_DTYPE_INT, 0, false);
        $this->initVar('hasGraph', \XOBJ_DTYPE_INT, 1, false);
    }

    // Required
    public $name = 'ticketsByUser';
    // Required
    public $meta       = [
        'name'        => \_XHELP_TBU_NAME,
        'author'      => 'Eric Juden',
        'authorEmail' => 'eric@3dev.org',
        'description' => \_XHELP_TBU_DESC,
        'version'     => '1.0',
        'dbFields'    => [
            'name'        => \_XHELP_TBU_DB1,
            'TicketCount' => \_XHELP_TBU_DB2,
        ],
    ];
    public $parameters = [
        \_XHELP_TBU_PARAM1 => [
            'controltype' => \XHELP_CONTROL_DATETIME,
            'fieldname'   => 'startDate',
            'value'       => '',      // last month
            'values'      => '',
            'fieldlength' => 25,
            'dbfield'     => 't.posted',
            'dbaction'    => '>',
        ],
        \_XHELP_TBU_PARAM2 => [
            'controltype' => \XHELP_CONTROL_DATETIME,
            'fieldname'   => 'endDate',
            'value'       => '',      // today
            'values'      => '',
            'fieldlength' => 25,
            'dbfield'     => 't.posted',
            'dbaction'    => '<=',
        ],
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
     $myReport .= "<img src='".XHELP_BASE_URL."/report.php?op=graph&name=ticketsByUser".$params."' align='center' width='500' height='300'>";
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

     $totalTickets = 0;
     foreach ($aResults as $result) {
     $myReport .= "<tr class='even'>";
     foreach ($dbFields as $dbField=>$field) {
     $myReport .= "<td>". $result[$dbField] ."</td>";

     if ($dbField == 'TicketCount') {
     $totalTickets += $result[$dbField];
     }
     }
     $myReport .= "</tr>";
     }

     // Display total tickets
     $myReport .= "<tr class='foot'><td>"._XHELP_TEXT_TOTAL."</td><td>". $totalTickets ."</td></tr>";

     $myReport .= "</table>";
     $myReport .= "</div>";

     return $myReport;
     }
     */

    /**
     * @return void
     */
    public function generateGraph()
    {
        if (0 == $this->getVar('hasResults')) {
            $this->_setResults();
        }
        $aResults = $this->getVar('results');

        $i    = 0;
        $data = [];
        foreach ($aResults as $result) {
            $data[0][] = $result['name'];
            $data[1][] = $result['TicketCount'];
        }

        $this->generatePie3D($data, 0, 1, \XHELP_IMAGE_PATH . '/graph_bg.jpg');
    }

    /**
     * @return bool
     */
    public function _setResults()
    {
        global $xoopsDB;
        // AND (t.posted > UNIX_TIMESTAMP(?)) AND (t.posted <= UNIX_TIMESTAMP(?))
        $sSQL = \sprintf('SELECT u.name, COUNT(t.id) AS TicketCount FROM `%s` u, %s t WHERE (u.uid = t.uid) %s GROUP BY u.name ORDER BY TicketCount DESC', $xoopsDB->prefix('users'), $xoopsDB->prefix('xhelp_tickets'), $this->extraWhere);

        $result   = $xoopsDB->query($sSQL);
        $aResults = $this->_arrayFromData($result);
        $this->setVar('results', \serialize($aResults));
        $this->setVar('hasResults', 1);

        return true;
    }
}
