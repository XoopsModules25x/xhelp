<?php
//

require_once XHELP_CLASS_PATH . '/report.php';
xhelpIncludeReportLangFile('staffRolesByDept');

global $xoopsDB;

/**
 * Class XHelpStaffRolesByDeptReport
 */
class XHelpStaffRolesByDeptReport extends xhelpReport
{
    /**
     * XHelpStaffRolesByDeptReport constructor.
     */
    public function __construct()
    {
        $this->initVar('results', XOBJ_DTYPE_ARRAY, null, false);
        $this->initVar('hasResults', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('hasGraph', XOBJ_DTYPE_INT, 0, false);
    }

    public $meta = [
        'name'         => _XHELP_SRD_NAME,
        'author'       => 'Eric Juden',
        'author_email' => 'eric@3dev.org',
        'description'  => _XHELP_SRD_DESC,
        'version'      => '1.0',
        'dbFields'     => [
            'Department' => _XHELP_SRD_DB3,
            'Role'       => _XHELP_SRD_DB2,
            'name'       => _XHELP_SRD_DB1
        ]
    ];

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
     $myReport .= "</div>";

     // Display report
     $myReport .= "<br>";
     $myReport .= "<div id='xhelp_report'>";
     $myReport .= "<table>";
     $myReport .= "<tr>";

     $dbFields = $this->meta['dbFields'];
     $myReport .= "<th>".$dbFields['Department']."</th>";
     $myReport .= "<th>".$dbFields['Role']."</th>";
     $myReport .= "<th>".$dbFields['name']."</th>";

     $myReport .= "</tr>";

     $dept = '';
     $role = '';
     foreach ($aResults as $result) {
     if ($result['Department'] != $dept) {
     $myReport .= "<tr class='even'><td>".$result['Department']."</td>";
     $dept = $result['Department'];
     $role = '';
     } else {
     $myReport .= "<tr class='even'><td></td>";
     }
     if ($result['Role'] != $role) {
     $myReport .= "<td>".$result['Role']."</td>";
     $role = $result['Role'];
     } else {
     $myReport .= "<td></td>";
     }
     $myReport .= "<td>".$result['name']."</td></tr>";
     }

     $myReport .= "</table>";
     $myReport .= "</div>";

     return $myReport;
     }
     */

    /**
     *
     */
    public function generateGraph()
    {
    }

    /**
     * @return bool
     */
    public function _setResults()
    {
        global $xoopsDB;

        $sSQL = sprintf(
            'SELECT u.name, r.name AS Role, d.department AS Department FROM %s u, %s s, %s sr, %s r, %s d WHERE (u.uid = s.uid) AND (u.uid = sr.uid) AND (sr.roleid = r.id) AND (sr.deptid = d.id) AND (u.uid = sr.uid) AND (u.uid = s.uid) ORDER BY d.department, u.name',
                        $xoopsDB->prefix('users'),
            $xoopsDB->prefix('xhelp_staff'),
            $xoopsDB->prefix('xhelp_staffroles'),
            $xoopsDB->prefix('xhelp_roles'),
            $xoopsDB->prefix('xhelp_departments')
        );

        $result   = $xoopsDB->query($sSQL);
        $aResults = $this->_arrayFromData($result);
        $this->setVar('results', serialize($aResults));
        $this->setVar('hasResults', 1);

        return true;
    }
}
